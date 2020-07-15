(function ($) {
    "use strict";

    var url = window.location;
    var element = $('.sidebar-menu li .dropdown-menu li a').filter(function() {
        return this.href == url;
    }).addClass('active');

    $(document).on('click', '.sidebar-menu li a.list-group-item-action', function(e){
        var $that = $(this);

        var hasDropdown = $that.siblings('.dropdown-menu').length;
        if (hasDropdown){
            e.preventDefault();
        }

        if ( ! $that.closest('li').hasClass('active')) {
            $that.siblings('ul').slideToggle();
            $that.find('.arrow i').toggleClass('open-icon');
        }
    });

    if (page_data.successfull_payment === true) {
        swal(
          'Payment Successfull!',
          'You have Successfully made a payment for your invoice',
          'success'
        ) 
    }

    if (page_data.successfull_payment === false) {
        swal(
              'Payment Failed!',
              'Unable to complete payment. Please try again',
              'error'
            )
    }

    // // Pusher Notifications
    //  $(document).ready(function(){
    //     var notificationsWrapper   = $('.dropdown-notifications');
    //     var notificationsToggle    = notificationsWrapper.find('a[data-toggle]');
    //     var notificationsCountElem = notificationsToggle.find('i[data-count]');
    //     var notificationsCount     = parseInt(notificationsCountElem.data('count'));
    //     var notifications          = notificationsWrapper.find('ul.dropdown-menu');

    //     if (notificationsCount <= 0) {
    //     notificationsWrapper.hide();
    //   }

    //   // Enable pusher logging - don't include this in production
    //   // Pusher.logToConsole = true;

    //       var pusher = new Pusher(page_data.pusher_app_key, {
    //         encrypted: true
    //       });

    //       // Subscribe to the channel we specified in our Laravel Event
    //       var channel = pusher.subscribe('job-flagged');

    //       // Bind a function to a Event (the full Laravel class)
    //       channel.bind('App\\Events\\JobFlagged', function(data) {
    //         console.log(data);
    //         var existingNotifications = notifications.html();
    //         var avatar = Math.floor(Math.random() * (71 - 20 + 1)) + 20;
    //         var newNotificationHtml = `
    //           <li class="notification active">
    //               <div class="media">
    //                 <div class="media-left">
    //                   <div class="media-object">
    //                     <span class="la la-user"></span>
    //                   </div>
    //                 </div>
    //                 <div class="media-body">
    //                   <strong class="notification-title">`+data.message+`</strong>
    //                   <!--p class="notification-desc">Extra description can go here</p-->
    //                   <div class="notification-meta">
    //                     <small class="timestamp">about a minute ago</small>
    //                   </div>
    //                 </div>
    //               </div>
    //           </li>
    //         `;
    //         notifications.html(newNotificationHtml + existingNotifications);

    //         notificationsCount += 1;
    //         notificationsCountElem.attr('data-count', notificationsCount);
    //         notificationsWrapper.find('.notif-count').text(notificationsCount);
    //         notificationsWrapper.show();
    //       });
    //  });
        

    // $(document).load(function(){
    //     $("#completed").modal("show");
    // })
   
    /*########################################
        PAYSTACK PAYMENT GATEWAY INTEGRATION #
     ########################################
     $(document).on('click', '.paystack_pay', function(){
        var amount = $(this).attr('id');
        var job_id = $(this).attr('title');
        var handler = PaystackPop.setup({
            key: 'pk_live_29502dd109b31178219d15782815563bebf478d2',
            email: page_data.user.email,
            amount: (amount*100),
            metadata: {
                custom_fields: [
                    {
                        display_name: "Mobile Number",
                        variable_name: "mobile_number",
                        value: phone
                    }
                ]
            },
            callback: function (response){
                //After a complete Transaction
                //window.location.href = page_data.routes.verify_payment;
                $.ajax({
                    url: page_data.routes.verify_payment,
                    type: 'POST',
                    data: {reference: response.reference, job_id : job_id, _token : page_data.csrf_token},
                    success: function (data){
                        $('#payment_success').modal('show');
                    },
                    error: function(data){
                        $('#payment_failure').modal('show');
                    }
                })
            },
            onClose: function () {
                //When user closes the payment modal
                alert('Transaction cancelled');
            }
        });
        //Open paystack payment Modal
        handler.openIframe();
     });*/

    $(document).on('change', '.country_to_state', function(e){
        e.preventDefault();

        var country_id = $(this).val();
        $.ajax({
            type : 'POST',
            url : page_data.routes.get_state_option_by_country,
            data : {country_id : country_id, _token : page_data.csrf_token},
            success: function(data){
                $('.state_options').html(data.state_options);
            }
        });
    });

    if (jQuery().datepicker){
        $('.date_picker').datepicker({
            format: "yyyy-mm-dd",
            todayHighlight: true,
            startDate: new Date(),
            autoclose: true
        });
    }
    if (jQuery().tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }

    $(document).on('click','.category_delete', function (e) {
        if (!confirm("Are you sure? its can't be undone")) {
            e.preventDefault();
            return false;
        }

        var selector = $(this);
        var data_id = $(this).data('id');

        $.ajax({
            type: 'POST',
            url : page_data.routes.delete_categories,
            data: {data_id: data_id, _token: page_data.csrf_token},
            success: function (data) {
                if (data.success == 1) {
                    selector.closest('tr').remove();
                }
            }
        });
    });

    /*$(document).on('click', '.mark-btn', function(e){
        e.preventDefault();
        var id = $(this).attr('id');
        $("#form"+id).submit();
    });*/

    $(document).on('click', '.assign', function(e){
        e.preventDefault();
        var that = $(this);
        that.addClass('faa-spin animated');
        var job_id = that.attr('id');
        var artisan_id = $('#assigned'+job_id).val();
        $.ajax({
                type : "GET",
                url : `http://127.0.0.1:8000/admin/assign_artisan/${artisan_id}/${job_id}`,
                success: res => {
                    if(res.success) {
                        that.parent('form').fadeOut(500, function(){
                            $('#artisan_assigned'+job_id).text(res.artisan.full_name);
                            alert('Job assigned');
                        });
                    }
                }
            });
    });

    // Generate new invoice area
    $(document).on('change', '#acct_id', function(e){
        e.preventDefault();
        document.querySelector('#loaderImg').removeAttribute('style');

        var acct_id = $(this).val();
        $.ajax({
            type : "GET",
            url : page_data.routes.get_account,
            data : {acct_id : acct_id, _token : page_data.csrf_token},
            success: function(data){
                document.querySelector('#loaderImg').style.display = 'none';
                //console.log(data.acct);
                //check for when provided acct_id is empty
                $('#user_id_area').html(data.acct);
                findServices(data.id);
            },
            error: function(data){
                $('#user_id_area').html('');
            }
        });
    });

    $(document).on('click', '.del_invoice_from_edit', function(e){
        e.preventDefault();
        //You could try removing the <tr> element ($(this).parent('tr').remove)MAYBE on delete from all temp
        var res = confirm("Are you sure?");
        if (res) {
            var inv_id = $(this).attr('id');
            $.ajax({
                type : "GET",
                url : page_data.routes.delete_invoice,
                data : {invoice_id : inv_id, _token : page_data.csrf_token},
                success: function(data){
                    window.location=data;
                }
            });
        }
        
    });

    /*
    Delete invoice from all invoices template
     */
    $(document).on('click', '.del_invoice_from_all', function(e){
        e.preventDefault();
        //You could try removing the <tr> element ($(this).parent('tr').remove)MAYBE on delete from all temp
        var res = confirm("Are you sure?");
        if (res) {
            //remove the the row it belongs to
            $(this).fadeOut(1000, function(){
                //fade complete
                $(this).closest('tr').remove();
            });

            var inv_id = $(this).attr('id');
            $.ajax({
                type : "GET",
                url : page_data.routes.delete_invoice_from_all,
                data : {invoice_id : inv_id, _token : page_data.csrf_token},
                success: function(data){
                    alert("Invoice deleted");
                }
            });
        }
        
    });

    $(document).on('click', '.in-progress', function(e){
        e.preventDefault(); 
        var status = "In-Progress";
        markJob(status, $(this));
        
    });

    $(document).on('click', '.completed', function(e){
        e.preventDefault();
        var status = "Completed";
        markJob(status, $(this));
        
    });

    $(document).on('click', '.pending', function(e){
        e.preventDefault();
        var status = "Pending";
        markJob(status, $(this));
        
    });

    $(document).on('click', '.cancelled', function(e){
        e.preventDefault();
        var status = "Cancelled";
        markJob(status, $(this));
        
    });

    function markJob(status, handler){

            $(handler).attr("title","");
            var job_id = $(handler).attr("id");
            //check if we're currently not on the all jobs template view
            if (Number($("table").attr("id")) === 0) {
                //then remove the marked job item
                $(handler).closest('tr').fadeOut(1500, function(){
                    //fade complete
                    $(handler).closest('tr').remove();
                });
                   
            }else{
                var job = $("#jab"+job_id);
                job.attr("class","");
                job.addClass(status);
                job.text(status);
                
                /*get the anchor tag inside the td and remove the done class 
                if exist to create the effect that only one
                status can be marked at a time
                */
                $("#job"+job_id+" a").removeClass("marked");
                $(handler).toggleClass("marked");
            }

            $.ajax({
                type : "GET",
                url : page_data.routes.mark_job,
                data : {job_id : job_id, status : status, _token : page_data.csrf_token},
                success: function(data){
                    alert("Job status changed");
                }
            });
    }


    function findServices(user_id){
        document.querySelector('#loaderImg2').removeAttribute('style');
        $.ajax({
            type : "GET",
            url : page_data.routes.get_services,
            data : {user_id : user_id, _token : page_data.csrf_token},
            success: function(data){
                document.querySelector('#loaderImg2').style.display = 'none';
                if (data.acct.length > 0) {
                    $('#job').empty();
                    $('#job').attr('disabled',false);
                    $('#job').append('<option value="">--Select Job--</option>');
                    data.acct.forEach(function(val){
                   $('#job').append('<option value="'+val.id+'">'+val.category+'</option>');
                    });
                }else{
                    $('#job').empty();
                    $('#job').append('<option value="" class="la la-frown-o">No data</option>');
                    $('#job').attr('disabled',true);
                }
            }
        });
    }
    // Determine if a service has an artisan assigned 
    //if not assign one 
     $(document).on("change", ".createInvoiceSelect", function(){
        document.querySelector('#artisanList').style.display = 'none';
        var service_id = $(this).val();
        // var category = $('#artisanInfo option:selected').text();
        if (service_id !== "") {
            $.ajax({
                type : "GET",
                url : page_data.routes.check_artisan_assigned,
                data : {service_id : service_id, _token : page_data.csrf_token},
                success: function(data){
                    if (data.success !== true) {
                        var artisanInfo = $("#artisanInfo");
                        artisanInfo.empty();
                        var artisanList = document.querySelector('#artisanList');
                        artisanList.removeAttribute('style');
                        artisanInfo.append('<option value="">Please Assign an Artisan</option>')
                        data.artisans.forEach(function(artisan){
                            artisanInfo.append('<option value="'+artisan.id+'">'+artisan.full_name+'</option>')
                        });
                    }
                }
            });
        }
        
    });

    $(document).on("change", "#street", function(){
        var location = $(this).val();
        location += ", "+$("#LGA").val();
        $("#vis-location").html("<b>"+location+"</b>");
    });

    //Reschedule meeting 
    $(document).on("change", "#job_select", function(){
        $("#result").addClass("show-result");

        var img = document.querySelector('#loaderImg');
        img.removeAttribute('style');
        img.style.position = 'absolute';
        $(this).attr('disabled',true);
        var job_id = $(this).val();

        $.ajax({
            type : "GET",
            url : page_data.routes.get_jobs_for_reschedule,
            data : {job_id : job_id, _token : page_data.csrf_token},
            success: function(data){
                
                document.querySelector("#loaderImg").style.display = "none";
                $("#job_select").attr('disabled',false);

                $("#result").removeClass("show-result");

                var date = new Date(data.visiting_date);
                $("#old_date").text(date.toDateString());
                $("#old_time").text(data.visiting_time);
            }
        });
    });

    // Flagged job validation fail popup
    
    if (page_data.flag_job_validation_fails !== null){
        $('#jobFlagModal'+page_data.flag_job_validation_fails).modal('show');
    }

    // Flagged job reply validation fail popup    
     if (page_data.flag_reply_validation_fails !== null){
        $(".reply"+page_data.flag_reply_validation_fails).modal('show');
    }


    $(document).ready(function() {
        // Add new invoice items
        // ######################################
        var max_fields      = 100;
        var wrapper         = $(".newItem");
        var add_button      = $(".add_form_field");
     
        var x = 1;
        $(add_button).click(function(e){
            e.preventDefault();
            
            if(x < max_fields){
                x++;
                $(wrapper).append('<div class="form-group row" ><label class="col-sm-3 control-label" for="item">Item *</label><div class="col-sm-3"><input type="text" name="item_name[]" id="item_name" class="form-control" placeholder="Name - Ex: Rim of Wire" /></div><div class="col-sm-2"><input type="number" name="item_price[]" id="item_price" class="form-control" placeholder="Price/Unit " /></div><div class="col-sm-2"><input type="number" name="item_qty[]" id="item_qty" class="form-control" placeholder="Quantity" /></div><a href="#" class="delete" style="color: red;"> <span class="fa fa-times-circle"></a></div>');
            }
      else
      {
      alert('You Reached the limits')
      }
        });
     
        $(wrapper).on("click",".delete", function(e){
            e.preventDefault(); $(this).parent('div').remove(); x--;
        })

        /*######################
        Add new items while editting
         */
     
        var i = 1;
        var max_fields           = 500;
        var wrapper_edit         = $(".new_item_edit");
        var add_button_edit      = $(".add_form_field_edit");
        
        $(add_button_edit).click(function(e){
            e.preventDefault();
            
            var no_items = document.querySelector('.no'); 
            if (no_items) {
                no_items.style.display = 'none';    
            }
            
            if(i < max_fields){
                i++;
                $(wrapper_edit).append('<div class="form-group row" ><label class="col-sm-3 control-label" for="item">Item *</label><div class="col-sm-3"><input type="text" name="item_name_new[]" id="item_name" class="form-control" placeholder="Name - Ex: Rim of Wire" /></div><div class="col-sm-2"><input type="number" name="item_price_new[]" id="item_price" class="form-control" placeholder="Price/Unit " /></div><div class="col-sm-2"><input type="number" name="item_qty_new[]" id="item_qty" class="form-control" placeholder="Quantity" /></div><a href="#" class="delete" style="color: red;"> <span class="fa fa-times-circle"></a></div>');
            }
            else
              {
              alert('You Reached the limits')
              }
            });
     
        $(wrapper_edit).on("click",".delete", function(e){
            e.preventDefault(); 
            if (i > 1) {
                $(this).parent('div').remove(); x--;
            }else{
                alert("There must be atleast one item in the invoice");
            }
        })


        //Ajax Delete Item on Edit
        $('.itemEdit').on("click",".delete", function(e){
            e.preventDefault(); 
            //$(this).parent('div').remove();
            //get the value of clicked element id attribute which has been uniquely set
            //var item_id = $(this).attr('id');
            /*swal({
              title: "Are you sure?",
              text: "This is an irriversible action",
              icon: "warning",
              buttons: true,
              dangerMode: true,
            })
            .then((willDelete) => {
              if (willDelete) {
                $(this).parent('div').remove();
                $.ajax({
                    type : "GET",
                    url : page_data.routes.delete_edit_invoice_item,
                    data : {id : item_id, _token : page_data.csrf_token},
                    success: function(data){

                    }
                });
            swal("Invoice item deleted", {
            icon: "success",
                });
              } //else {
               // swal("Your imaginary file is safe!");
              //}
            });*/
            //check if theres only one item left in the invoice 
            if (document.querySelectorAll("#item").length === 1) {
                alert("There has to be atleast one item on the invoice");
            }else{
                var res = confirm("Are you sure?");
                if (res) {

                    var item_id = $(this).attr('id');
                    $(this).parent('div').remove();

                    $.ajax({
                        type : "GET",
                        url : page_data.routes.delete_edit_invoice_item,
                        data : {id : item_id, _token : page_data.csrf_token},
                        success: function(data){
                            alert("Item deleted!");
                        }
                    });
                }
            }
            
        })

    });




    /**
     * Settings Panel
     */
    $('.ajax-updating input[type="checkbox"], input[type="radio"]').click(function(){
        var input_name = $(this).attr('name');
        var input_value = 0;
        if ($(this).prop('checked')){
            input_value = $(this).val();
        }
        $.ajax({
            url : page_data.routes.save_settings,
            type: "POST",
            data: { [input_name]: input_value, _token: page_data.csrf_token},
        });
    });
    $('.ajax-updating input[name="date_format"]').click(function(){
        $('#date_format_custom').val($(this).val());
    });
    $('.ajax-updating input[name="time_format"]').click(function(){
        $('#time_format_custom').val($(this).val());
    });
    /**
     * Send settings option value to server
     */
    $('.ajax-updating #settings_save_btn').click(function(e){
        e.preventDefault();

        var $that = $(this);

        var form_data = $that.closest('form').serialize();
        $.ajax({
            url : page_data.routes.save_settings,
            type: "POST",
            data: form_data,
            beforeSend : function () {
                $that.attr('disabled', 'disabled');
                $that.addClass('updating-btn');
            },
            success : function (data) {

            },
            complete: function () {
                $that.removeClass('updating-btn');
                $that.removeAttr('disabled');
            }
        });
    });

    /**
     * show or hide stripe and paypal settings wrap
     */
    $('#enable_paypal').click(function(){
        if ($(this).prop('checked')){
            $('#paypal_settings_wrap').slideDown();
        }else{
            $('#paypal_settings_wrap').slideUp();
        }
    });
    $('#enable_stripe').click(function(){
        if ($(this).prop('checked')){
            $('#stripe_settings_wrap').slideDown();
        }else{
            $('#stripe_settings_wrap').slideUp();
        }
    });

    $('#enable_bank_transfer').click(function(){
        if ($(this).prop('checked')){
            $('.bankPaymetWrap').slideDown();
        }else{
            $('.bankPaymetWrap').slideUp();
        }
    });


})( jQuery );