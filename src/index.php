<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body class="bg-light">
    
    <div class="container">
    <main>
        <div class="row justify-content-center mt-4">
            <div class="col-sm-6 border">
                <form action="upload.php" method="post" enctype="multipart/form-data">
                    <div class="row pb-4 justify-content-center">
                        <div class="col-sm-12">
                            <div class="lead">Para fazer uma assinatura única: </div>
                        </div>
                    </div>
                    <div class="row pb-4 justify-content-center">
                        <div class="col-sm-12">
                            <label for="fileToUpload" class="form-label">Selecione um arquivo:</label>
                            <input class="form-control form-control-sm" name="fileToUpload" id="fileToUpload" type="file" required />
                        </div>
                    </div>
                    <div class="row pb-4 justify-content-center">
                        <div class="col-sm-6">
                            <input type="submit" class="btn btn-primary btn-lg  w-100" value="Assinar" name="submit">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row justify-content-center mt-4">
            <div class="col-sm-6 border">
                <form action="upload.php" method="post" enctype="multipart/form-data">
                    <div class="row pb-4 justify-content-center">
                        <div class="col-sm-12">
                            <div class="lead">Para fazer assinaturas em lote: </div>
                        </div>
                    </div>
                    <div class="row pb-4 justify-content-center">
                        <div class="col-sm-12">
                            <label for="multipleFilesToUpload" class="form-label">Selecione múltiplos arquivos:</label>
                            <input class="form-control" type="file" id="multipleFilesToUpload" name="multipleFilesToUpload[]" multiple required />
                        </div>
                    </div>
                    <div class="row pb-4 justify-content-center">
                        <div class="col-sm-6">
                            <input type="submit" class="btn btn-primary btn-lg w-100" value="Assinar em lote" name="submit">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

</div>

</body>
</html>
