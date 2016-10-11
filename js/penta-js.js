$(document).ready(function () {
    $('#zon').hide();
    $('#lokasi').hide();
    $('#taman').hide();
    $('#slide').click(function () {
        var hidden = $('.hidden');
        var tugas = $('.tugas');
        if (hidden.hasClass('visible')) {
            hidden.animate({
                "left": "-25vw"
            }, "slow").removeClass('visible');
        }
        else {
            hidden.animate({
                "left": "0px"
            }, "slow").addClass('visible');
        }
    });
});
$('#dropdown1 li').click(function () {
    var semasa = $('#jenis').text();
    var jenis = $(this).text();
    $('#jenis').html(jenis);
    if (jenis == "Jenis Taman") {
        $('#zon').hide();
        $('#lokasi').hide();
        $('#taman').hide();
    }
    else if (jenis == "Taman Awam") {
        $('#zon').hide();
        $('#lokasi').hide();
        $('#taman').show();
        $('#taman').html("Pilih Taman");
    }
    else {
        $('#zon').show();
        $('#lokasi').hide();
        $('#taman').hide();
        $('#zon').html("Pilih Zon");
    }
});
$('#dropdown2 li').click(function () {
    var semasa = $('#zon').text();
    var jenis = $(this).text();
    $('#zon').html(jenis);
    if (jenis == "Pilih Zon") {
        $('#lokasi').hide();
        $('#taman').hide();
    }
    else {
        $('#lokasi').show();
        $('#lokasi').html("Pilih Lokasi");
    }
});
$('#dropdown3 li').click(function () {
    var semasa = $('#lokasi').text();
    var jenis = $(this).text();
    $('#lokasi').html(jenis);
    if (jenis == "Pilih Lokasi") {
        $('#taman').hide();
    }
    else {
        $('#taman').show();
        $('#taman').html("Pilih Taman");
    }
});
$('#dropdown4 li').click(function () {
    console.log($(this).text());
    $('#taman').html($(this).text());
});
$('.lihat li i').click(function () {
    var pilih = $(this).text();
    if (pilih == "verified_user") {
        $('#stat').attr({
            'data-tooltip': "Sangat Baik"
        });
    }
    else if (pilih == "thumb_up") {
        $('#stat').attr('data-tooltip', 'Baik');
    }
    else if (pilih == "thumbs_up_down") {
        $('#stat').attr('data-tooltip', 'Sederhana');
    }
    else if (pilih == "thumb_down") {
        $('#stat').attr('data-tooltip', 'Teruk');
    }
    else {
        $('#stat').attr('data-tooltip', 'Semua');
    }
    $('.nampak').html(pilih);
});