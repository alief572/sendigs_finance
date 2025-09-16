<input type="date" class="tanggal" name="" id="">
<input type="text" name="" id="id_po" readonly>
<input type="text" name="" id="id" readonly>
<button type="button" class="btn btn-sm btn-primary sub_mit">Submit</button>

<script>
    $(document).on('click', '.sub_mit', function() {
        var tanggal = $('.tanggal').val();

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'sub_mit',
            data: {
                'tanggal': tanggal
            },
            cache: false,
            dataType: 'JSON',
            success: function(result) {
                $('#id_po').val(result.id_po);
                $('#id').val(result.id);
            }
        });
    });
</script>