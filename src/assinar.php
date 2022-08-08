<?php
session_start();

################################################
# Usa as URIs de acesso aos serviços conforme configuradas
# no arquivo config.php
include_once "config.php";

################################################
# Inicio do handshake OAuth
$code = $_GET['code'];
if (!$_SESSION['code_used']) {
    $_SESSION['code_used'] = false;
}
if (!$code || $_SESSION['code_used']) {
  // 1. Pedir autorização ao cidadão
  // Para realização da assinatura em lote, o intuito deve ser informado ao cidadão no
  // momento do pedido de autorização. Isto é feito usando-se o scope 'signature_session'.
  // Para realização de uma única assinatura deve-se usar o escopo 'sign'.
  // Detalhe: CODE só pode ser usado uma vez. Se $_SESSION['code_used'] = true
  // Pedimos nova autorização 
  $scope = $_SESSION['lote'] ? 'signature_session' : 'sign';
  $authorizeUri = "https://$servidorOauth/authorize" .
                                        "?response_type=code" .
                                        "&redirect_uri=" . urlencode($redirect_uri) .
                                        "&scope=$scope" .
                                        "&client_id=$clientid";

  $_SESSION['code_used'] = false;
  header("Location: $authorizeUri"); /* Redirect browser */
  exit;
} else {
  // 2. Obter access token a partir do código de autorização
  $accessTokenUri = $tokenUri .
                "?code=$code" .
                "&client_id=$clientid" .
                "&grant_type=authorization_code" .
                "&client_secret=$secret" .
                "&redirect_uri=" . urlencode($redirect_uri);

  $options = array(
      'http' => array(
          'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
          'method'  => 'POST'
      )
  );
  
  $context  = stream_context_create($options);
  $result = file_get_contents($accessTokenUri, false, $context);
  $response = json_decode($result);
  $AT = $response->access_token;
  $_SESSION['code_used'] = true;
  ################################################
  # Início das operações criptográficas

  // 3. Fazer download do certificado público
  
  $options = array(
      'http' => array(
          'header'  => "Authorization: Bearer $AT\r\n",
          'method'  => 'GET'
      )
  );

  $context  = stream_context_create($options);
  $certBase64 = file_get_contents($certificateUri, false, $context);


  // 4. Fazer a operação de criptográfica de assinatura
  // No caso de assinaturas em lote, deve-se realizar uma chamada da operação de assinatura por documento.

  // Hash deve ser SHA256
  $hashes = $_SESSION['hashes'];
  $assinaturas = [];

  foreach ($hashes as $arquivo => $hash) {
    $pacoteAssinatura = json_encode(array('hashBase64' => base64_encode($hash)));
    $options = array(
      'http' => array(
          'header'  => "Content-type: application/json\r\n" .
          "Authorization: Bearer $AT\r\n",
          'method'  => 'POST',
          'content' => $pacoteAssinatura
          )
        );
    $context  = stream_context_create($options);
    $assinaturaBase64 = file_get_contents($signingUri, false, $context);
    $assinaturas[$arquivo] = $assinaturaBase64;
  }

  function makeLapoLink($base64) {
    return 'http://lapo.it/asn1js/#' . preg_replace('/(-----[\s\w]+-----)|\n/m', '', $base64);
  }
  ?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container">
    <main>
        <div class="row justify-content-center mt-4">
            <div class="col-sm-8 border">
                <div class="row pb-4 justify-content-center">
                    <div class="col-sm-12">
                        <div class="lead">Certificado Público: </div>
                    </div>
                </div>
               
                <div class="row pb-4 justify-content-center">
                    <div class="col-sm-12">
                        <div class="input-group">
                            <textarea class="form-control" id="certificado" rows="10" readonly><?php echo $certBase64 ?></textarea>
                            <a href="<?php echo makelapoLink($certBase64) ?>" target="_blank" class="input-group-text"><i class="bi bi-arrow-up-right-square"></i></a>
                            <a download="certificado.crt" class="input-group-text" href="data:text/plain;base64,<?php echo base64_encode($certBase64) ?>"><i class="bi bi-cloud-download"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mt-4">
            <div class="col-sm-8 border">
                <div class="row pb-4 justify-content-center">
                    <div class="col-sm-12">
                        <div class="lead">Assinaturas PKCS#7: </div>
                    </div>
                </div>
               
                <?php foreach ($assinaturas as $arquivo => $assinatura) { ?>
                <div class="row pb-4 justify-content-center">
                    <div class="col-sm-2">
                        <span class="text-break"><?php echo $arquivo ?></span>
                    </div>
                    
                    <div class="col-sm-10 ">
                        <div class="input-group">
                            <textarea class="form-control" rows="5" readonly><?php echo base64_encode($assinatura) ?></textarea>
                            <a href="<?php echo makelapoLink(base64_encode($assinatura)) ?>" target="_blank" class="input-group-text"><i class="bi bi-arrow-up-right-square"></i></a>
                            <a download="<?php echo $arquivo ?>.p7s" class="input-group-text" href="data:text/plain;base64,<?php echo base64_encode($assinatura) ?>"><i class="bi bi-cloud-download"></i></a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <div class="row justify-content-center mt-4">
            <div class="col-sm-8">
                <div class="row pb-4 justify-content-center">
                    <div class="col-sm-6">
                        <a href="/" class="btn btn-secondary btn-lg w-100">Voltar</a>
                    </div>
                </div>
               
                
            </div>
        </div>



    </div>
</body>
</html>

  <?php
}
?>
