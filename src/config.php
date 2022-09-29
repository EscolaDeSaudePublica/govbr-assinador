<?php

################################################
# Conf desenv govbr
$redirect_uri = "http://assinador.dev.org.br/assinar.php";
$clientid = "devLocal";
$secret = "younIrtyij3";
$servidorOauth = "cas.staging.iti.br/oauth2.0"; #govbr
$servidorNuvemQualificada = "assinatura-api.staging.iti.br/externo/v2";

$tokenUri="https://$servidorOauth/token";
$certificateUri = "https://$servidorNuvemQualificada/certificadoPublico";
$signingUri = "https://$servidorNuvemQualificada/assinarPKCS7";

?>
