<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Cloudinary</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .test-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            margin: 50px auto;
        }
        .preview-image {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .custom-file-upload {
            border: 2px dashed #667eea;
            display: inline-block;
            padding: 30px;
            cursor: pointer;
            width: 100%;
            text-align: center;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .custom-file-upload:hover {
            border-color: #764ba2;
            background: #f8f9fa;
        }
        #file-input {
            display: none;
        }
        .result-box {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: none;
        }
        .result-box.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .result-box.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .url-box {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            word-break: break-all;
            margin-top: 10px;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h2 class="text-center mb-4">🖼️ Test de Cloudinary</h2>
        <p class="text-center text-muted">Sube una imagen para probar la integración</p>

        <form id="uploadForm">
            @csrf
            <label for="file-input" class="custom-file-upload">
                <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: #667eea;"></i>
                <br>
                <span id="file-name">Haz click o arrastra una imagen aquí</span>
                <br>
                <small class="text-muted">JPG, PNG o GIF - Máximo 5MB</small>
            </label>
            <input id="file-input" type="file" name="imagen" accept="image/*" required>

            <button type="submit" class="btn btn-primary btn-block btn-lg mt-3" id="btnSubir">
                <i class="fas fa-upload"></i> Subir Imagen a Cloudinary
            </button>
        </form>

        <div id="result" class="result-box"></div>

        <div id="preview-container" style="display:none;">
            <h5 class="mt-4">✅ Imagen Subida:</h5>
            <img id="preview" class="preview-image" src="" alt="Preview">
            <div class="url-box" id="urlBox"></div>
            <small class="text-muted d-block mt-2">
                <i class="fas fa-info-circle"></i> Esta URL la guardarás en tu base de datos
            </small>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#file-input').on('change', function() {
                const fileName = this.files[0] ? this.files[0].name : 'Haz click o arrastra una imagen aquí';
                $('#file-name').text(fileName);
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#uploadForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const btnSubir = $('#btnSubir');

                btnSubir.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Subiendo a Cloudinary...');
                $('#result').hide();
                $('#preview-container').hide();

                $.ajax({
                    url: '/upload-test',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('Respuesta:', response);

                        btnSubir.prop('disabled', false).html('<i class="fas fa-upload"></i> Subir Imagen a Cloudinary');

                        if(response.success) {
                            $('#result')
                                .removeClass('error')
                                .addClass('success')
                                .html('<strong><i class="fas fa-check-circle"></i> ' + response.message + '</strong>')
                                .fadeIn();

                            $('#preview').attr('src', response.url);
                            $('#urlBox').html('<strong>URL generada:</strong><br>' + response.url);
                            $('#preview-container').fadeIn();

                            $('#uploadForm')[0].reset();
                            $('#file-name').text('Haz click o arrastra una imagen aquí');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);

                        btnSubir.prop('disabled', false).html('<i class="fas fa-upload"></i> Subir Imagen a Cloudinary');

                        let errorMsg = 'Error al subir la imagen';
                        if(xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        $('#result')
                            .removeClass('success')
                            .addClass('error')
                            .html('<strong><i class="fas fa-times-circle"></i> ' + errorMsg + '</strong>')
                            .fadeIn();
                    }
                });
            });
        });
    </script>
</body>
</html>
