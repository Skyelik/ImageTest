$('#uploadForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        type: 'POST',
        url: 'ajax.php',
        data: new FormData(this),
        processData: false,
        contentType: false,
        success: function(response) {
            $('#message').html(response.message);
            if (response.success) {
                loadImages(); 
            }
        },
        error: function() {
            $('#message').html('Ошибка при загрузке изображения.');
        }
    });
});

function loadImages(sortOrder = 'upload_date DESC') {
    $.ajax({
        url: 'ajax.php',
        method: 'GET',
        data: { action: 'load_images', sort: sortOrder },
        success: function(response) {
            $('#imageGallery').html(response); 
        }
    });
}

$(document).ready(function() {
    loadImages();

    $('#sortOrder').on('change', function() {
        loadImages($(this).val());
    });
});
