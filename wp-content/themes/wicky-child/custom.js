
        jQuery(document).ready(function($) {
            $(".set > a").on("click", function() {
                if ($(this).hasClass("active")) {
                $(this).removeClass("active");
                $(this)
                    .siblings(".content")
                    .slideUp(200);
                $(".set > a i")
                    .removeClass("fa-minus")
                    .addClass("fa-plus");
                } else {
                $(".set > a i")
                    .removeClass("fa-minus")
                    .addClass("fa-plus");
                $(this)
                    .find("i")
                    .removeClass("fa-plus")
                    .addClass("fa-minus");
                $(".set > a").removeClass("active");
                $(this).addClass("active");
                $(".content").slideUp(200);
                $(this)
                    .siblings(".content")
                    .slideDown(200);
                }
            });

           /* $('.repeater').repeater({
                initEmpty: false,
                defaultValues: {
                    'text-input': 'foo'
                },
                show: function () {
                    $(this).slideDown();
                },
                hide: function (deleteElement) {
                    if(confirm('Are you sure you want to delete this element?')) {
                        $(this).slideUp(deleteElement);
                    }
                },
                ready: function (setIndexes) {
                },
                isFirstItemUndeletable: true
            });*/

            $('#upload_csv').on('click', function(){
                //on change event  
                formdata = new FormData();
                var url = $('#gl_code').val()
                var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
            
                if($('#gl_code').prop('files').length > 0 && ext == "csv")
                {
                    file =$('#gl_code').prop('files')[0];
                    formdata.append("gl_code", file);  
                    formdata.append("action", 'add_gl_code');  
                    $('#loader').show();      
                    $(this).attr('disabled', true);
                    $.ajax({
                        url : ajaxurl,
                        type : 'post',
                        data : formdata,
                        processData: false,
                        contentType: false,
                        success : function( response ) {
                            var obj = JSON.parse(response);
                            $('#upload_csv').prop('disabled', false);
                            $('#loader').hide();
                            $('#gl_code').val('');
                            $('#success_msg').show();
                            setTimeout(function(){ $('#success_msg').hide(); }, 2000);
                        }
                    });
                }else{
                    alert('Please upload CSV file.');
                }
            });

            $("#update_inventory").on('click', function(){
                var sku = $("#inventory_sku").val();
                $("#responce").html("Updating...");
                $.ajax({
                    url : ajaxurl+"?action=update_stock_by_sku&sku="+sku,
                    type : 'get',
                    processData: false,
                    contentType: false,
                    success : function( response ) {
                        $("#responce").html(response);
                    }
                });
            });
        });
