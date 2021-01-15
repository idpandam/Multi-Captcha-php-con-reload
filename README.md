# Multi-Captcha-php-con-reload
Una classe in php per generare diversi captcha in una stesssa pagina. Possibilità di reload con ajax.

Gli esempi riportati in index.php salvano i codici captcha in una sessione, utilizzando la classe includes/class-secureSession.php

E' possibile salvare in un db mysql ultilizzando la classe includes/class-mysqlSession.php:
- configurare l'accesso al db modificando il file config.php
- creare in mysql tabella simile a db/pa_session.sql
- aggiornare la class-mysqlSession.php con il nome della tabella appena creata (se è stato modificato)
- in include/class-captcha.php modificare nel metodo CaptchaStartSession la riga $configSession=self::ConfigSession('file'); con $configSession=self::ConfigSession('db');

Nel caso in cui una sessione è già presente passare il nome della sessione sia in index.php che in validate.php (vedi esempio riportato ma commentato)
