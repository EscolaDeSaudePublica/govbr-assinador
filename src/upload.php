<?php
if(!isset($_POST["submit"])) {
    header("Location: index.php");
    exit;
}

session_start();

################################################
# Caso estejamos tratanto de uma assinatura única - Usaremos escopo sign
if ($_FILES["fileToUpload"]) {
    $data = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);
    $_SESSION['lote'] = false;
    $_SESSION['files'] = [$_FILES["fileToUpload"]["name"] => $data];
} else if ($_FILES["multipleFilesToUpload"]) {
    ################################################
    # Caso estejamos tratanto de assinaturas em lote 

    $_SESSION['lote'] = true;
    $file_count = count($_FILES["multipleFilesToUpload"]["name"]);
    $_SESSION['files'] = [];
    for ($i=0; $i < $file_count; $i++) {
        $data = file_get_contents($_FILES["multipleFilesToUpload"]["tmp_name"][$i]);
        $_SESSION['files'][$_FILES["multipleFilesToUpload"]["name"][$i]] = $data;
    }
}

header("Location: assinar.php");

?>