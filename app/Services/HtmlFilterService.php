<?php

namespace App\Services;

use DOMDocument;
use Illuminate\Database\Eloquent\Collection;

class HtmlFilterService
{
    public function filterHtml($html)
    {
        // Normalizza input nullo/vuoto per evitare warning su loadHTML
        $html = $html ?? '';

        // Crea parser DOM
        $doc = new DOMDocument();

        // Disabilita warning HTML malformato (tipico da input utente)
        libxml_use_internal_errors(true);

        // Carica HTML senza wrapper html/body automatici
        $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Pulisce errori libxml accumulati
        libxml_clear_errors();

        // Tag ad alto rischio XSS da rimuovere completamente
        $dangerousTags = ['script', 'iframe', 'object', 'embed'];

        foreach ($dangerousTags as $tagName) {
            $nodes = $doc->getElementsByTagName($tagName);

            // Iterazione inversa: evita problemi con NodeList "live" durante removeChild
            for ($i = $nodes->length - 1; $i >= 0; $i--) {
                $node = $nodes->item($i);
                if ($node && $node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        // Scansiona tutti i nodi per rimuovere attributi pericolosi
        $allNodes = $doc->getElementsByTagName('*');

        for ($i = 0; $i < $allNodes->length; $i++) {
            $node = $allNodes->item($i);

            if (!$node || !$node->hasAttributes()) {
                continue;
            }

            $attributesToRemove = [];

            foreach ($node->attributes as $attribute) {
                $name = strtolower($attribute->nodeName);
                $value = strtolower($attribute->nodeValue);

                // Blocca event handler inline (onclick, onerror, onload, ...)
                if (str_starts_with($name, 'on')) {
                    $attributesToRemove[] = $name;
                }

                // Blocca javascript: in href/src (payload XSS classico)
                if (in_array($name, ['href', 'src']) && str_starts_with(trim($value), 'javascript:')) {
                    $attributesToRemove[] = $name;
                }
            }

            // Rimuove solo dopo il loop per non invalidare l'iterazione degli attributi
            foreach ($attributesToRemove as $attributeName) {
                $node->removeAttribute($attributeName);
            }
        }

        // Restituisce HTML sanificato da salvare/renderizzare
        return $doc->saveHTML();
    }

    public function filterHtmlCollectionByField(Collection $collection, string $key)
    {
        // Utility: filtra un campo HTML su ogni elemento della collection
        return $collection->map(function ($item) use ($key) {
            $item->$key = $this->filterHtml($item->$key);
            return $item;
        });
    }
}