function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
  var expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(";");
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == " ") {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

jQuery(document).ready(function ($) {
 
  setTimeout(popup, 1000);
  function popup() {
    if (getCookie("users_compliance_state") == "") {
      $("#contactdiv").css("display", "block");
    }
  }
  $("#onclick").click(function () {
    $("#contactdiv").css("display", "block");
  });
  $("#contact #cancel").click(function () {
    $(this).parent().parent().hide();
  });

  $(document).on("click", "#noaddress", function () {
    $("#noaddressdiv").css("display", "block");
  });
  $(document).on("click", "#canceladd", function () {
    $(this).parent().parent().hide();
  });

  $("#user_type_select ").on("change", function () {
    var role = $(this).val();
    $.ajax({
      url: readmelater_ajax.ajax_url,
      type: "post",
      data: {
        action: "get_questions",
        role: role,
      },
      success: function (response) {
        $("#checkout_ques").html(response);
        if (role.indexOf("club") !== -1) {
          $("#customer_details .woocommerce-billing-fields").hide();
          $("#customer_details .col-1 h4").remove();
          $("#customer_details .col-1").append(
            "<h4>Shipping address not required for clubs</h4>"
          );
        } else {
          $("#customer_details .woocommerce-billing-fields").show();
          $("#customer_details .col-1 h4").remove();
        }
      },
    });
  });

  
  // Login form popup login-button click event.
  $("#loginbtn").click(function () {
    var name = $("#username").val();
    var password = $("#password").val();
    if (username == "" || password == "") {
      alert("Username or Password was Wrong");
    } else {
      $("#logindiv").css("display", "none");
    }
  });
  
  


  $(document).on("click", "#recheck_club", function () {
    $("#user_type_select").trigger("change");
  });


  $("#user_type, #user_state").on("change", function () {
    var type = $("#user_type").val();
    var state = $("#user_state").val();
    if (type != "" && state != "") {
      $.ajax({
        url: readmelater_ajax.ajax_url,
        type: "post",
        data: {
          action: "check_ship_popup",
          type: type,
          state: state,
        },
        success: function (response) {
          $("#ship_responce").html(response);
        },
      });
    }
  });

  
  $("#billing_state").on("change", function () {
    if ($(this).val() == "MS") {
      $("#billing_address_1").prop("readonly", true);
      $("#billing_address_1").val("1286 Gluckstadt Rd");
      $("#billing_address_2").prop("readonly", true);
      $("#billing_company").val("Mississippi Alcoholic Beverage Control");
      $("#billing_company").prop("readonly", true);
      $("#billing_address_2").val("");
      $("#billing_city").prop("readonly", true);
      $("#billing_city").val("Madison");
      $("#billing_postcode").prop("readonly", true);
      $("#billing_postcode").val("39110");
    } else {
      $("#billing_address_1").prop("readonly", false);
      $("#billing_address_2").prop("readonly", false);
      $("#billing_city").prop("readonly", false);
      $("#billing_postcode").prop("readonly", false);
      $("#billing_company").prop("readonly", false);
    }
    if ($(this).val() == "International") {
      $("#place_order").show();
      $("#check_ship_compliance").hide();
    }
  });

  $(".search_form").append(
    '<input type="hidden" name="post_type" value="product" />'
  );

  $(document).on("click", "li a.remove_from_cart_button", function () {
    var product_id = $(this).attr("data-product_id");
    $(document)
      .find('a.add_to_cart_button[data-product_id="' + product_id + '"]')
      .removeClass("added");
    $(document)
      .find('a.add_to_cart_button[data-product_id="' + product_id + '"]')
      .next("a")
      .remove();
  });

  $("#address_csv").on("change", function () {
    //on change event
    formdata = new FormData();
    var url = $(this).val();
    var ext = url.substring(url.lastIndexOf(".") + 1).toLowerCase();

    if ($(this).prop("files").length > 0) {
      if (ext == "csv") {
        file = $(this).prop("files")[0];
        _nonce = $("#_add_address_csv").val();
        formdata.append("address_csv", file);
        formdata.append("_nonce", _nonce);
        formdata.append("action", "add_address_csv");
        if ($("#add_address_view").length) {
          $("#add_address_view table").remove();
        }
        $("#loader").show();
        $.ajax({
          url: readmelater_ajax.ajax_url,
          type: "post",
          data: formdata,
          processData: false,
          contentType: false,
          success: function (response) {
            var obj = JSON.parse(response);
            $("#add_address_csv_name").val(obj.file_name);
            $("#add_address_view").show();
            $("#add_address_view").append(obj.html);
            $("#customer_details .woocommerce-billing-fields").hide();
            $("#customer_details .col-1 h4").remove();
            $("#customer_details .col-1").append(
              "<h4>Shipping address not required for clubs</h4>"
            );
            $("#loader").hide();
          },
        });
      } else {
        alert("Please upload CSV file.");
      }
    }
  });

  
  $("#update_inventory").on("click", function () {
    var sku = $("#inventory_sku").val();
    $("#responce").html("Updating...");
    $.ajax({
      url: readmelater_ajax.ajax_url + "?action=update_stock_by_sku&sku=" + sku,
      type: "get",
      processData: false,
      contentType: false,
      success: function (response) {
        $("#responce").html(response);
      },
    });
  });

});
