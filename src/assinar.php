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

  $urlApiESP = "http://127.0.0.1:8081/signPdf/$code";
  $urlLoteApiESP = "http://127.0.0.1:8081/signPdf/lote/$code";

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  if ($_SESSION['lote']) {
    $headers = [
        'Content-Type: multipart/form-data',
        'User-Agent: '.$_SERVER['HTTP_USER_AGENT'],
    ];
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_URL, $urlLoteApiESP);
    $filesToSign = array();

    foreach ($_SESSION['files'] as $fileName => $fileData) {
        $fileFullName = __DIR__."/tmp/$fileName";
        file_put_contents($fileFullName, $fileData);
        array_push($filesToSign, new CURLFILE($fileFullName));
    }

    $post_data = array('pdfs'=> $filesToSign);
    print_r($post_data);

    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
  
    $signedFilesZip = curl_exec($curl);
    var_dump($signedFilesZip);
    var_dump(curl_error($curl));
    // $signedFilesZip = json_decode(curl_exec($curl), TRUE);
    // $signedFiles = $signedFilesZip;//descompacta signedFilesZip;

    // foreach ($signedFiles as $signedFile) {
        $signFilePath = $fileFullName.'_Assinado.zip';
        file_put_contents($signFilePath, $signedFilesZip);
    // }

  } else {

    curl_setopt($curl, CURLOPT_URL, $urlApiESP);

    foreach ($_SESSION['files'] as $fileName => $fileData) {
        $fileFullName = __DIR__."/tmp/$fileName";
        file_put_contents($fileFullName, $fileData);
        $fileToSign = new CURLFILE($fileFullName);
    }

    $post_data = array('pdf'=> $fileToSign);

    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
  
    $signedFile = curl_exec($curl);
  
    $signedFileFullName = $fileFullName.'_Assinado.pdf';
    file_put_contents($signedFileFullName, $signedFile);
  }

  curl_close($curl);  
  $_SESSION['code_used'] = true;

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
                        <div class="lead">Arquivo(s) Assinado(s): </div>
                    </div>
                </div>
               
                <?php foreach ($_SESSION['files'] as $fileName => $fileData) { ?>
                <div class="row pb-4 justify-content-center">                    
                    <div class="col-sm-10 ">
                        <div class="input-group">
                            <textarea class="form-control" rows="1" readonly><?php echo $fileName ?></textarea>
                            <a download="<?php echo 'tmp/'.$fileName.'_Assinado.pdf' ?>" class="input-group-text" href="<?php echo 'tmp/'.$fileName.'_Assinado.pdf' ?>"><i class="bi bi-cloud-download"></i></a>
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
