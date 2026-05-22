
$("#user_sign_up_form").on("submit", function (e) {
  $("#saveBtn").prop("disabled", true);
  $("#loader").show();
  e.preventDefault();

  $.ajax({
    url: "apps/auth/controller/AddUserController.php",
    method: "POST",
    data: new FormData(this),
    contentType: false,
    dataType:"json",
    cache: false,
    processData: false,
    success: function (data) {
      $.toast({
        heading: "Laborow",
        text: data.message,
        icon: "info",
        loader: true, // Change it to false to disable loader
        position: "top-right",
        loaderBg: "#E31D1D", // To change the background
      });

      $("#loader").hide();

      setInterval("location.reload()", 3000);

      $("#saveBtn").prop("disabled", false);
    },
  });
});

$("#login_user_frm").on("submit", function (e) {
  $("#loader").show();
  $("#saveBtn").prop("disabled", true);
  e.preventDefault();

  let this_sec_code = $("#sec_code").val();
  let entered_sec_value = $("#enter_sec_code").val();

  if (this_sec_code === entered_sec_value) {
    $.ajax({
      url: "apps/auth/controller/CTRLUserLogin.php",
      method: "POST",
      data: new FormData(this),
      contentType: false,
      dataType:"json",
      cache: false,
      processData: false,
      success: function (data) {
        $.toast({
          heading: "Laborow",
          text: data.message,
          icon: "info",
          loader: true, // Change it to false to disable loader
          position: "top-right",
          loaderBg: "#E31D1D", // To change the background
        });

        if (data.message == "Login Successful") {
          setInterval(() => {
            window.location = "account";
          }, 4000);
        } else {
          $("#loader").hide();
          $("#saveBtn").prop("disabled", false);
        }
      },
    });
  } else {
    $("#response_here")
      .text("Security Code Error")
      .css("color", "#FD6E6E", "font-weight: bold");
    $("#loader").hide();
  }
});



$("#update_my_password_frm").on("submit", function (e) {
  $("#loader").show();
  $("#saveBtnModal").prop("disabled", true);
  e.preventDefault();
  $.ajax({
    url: "auth/controller/UpdateMyPasswordController.php",
    method: "POST",
    data: new FormData(this),
    contentType: false,
    cache: false,
    processData: false,
    success: function (data) {
      Snackbar.show({
        text: data,
        actionTextColor: "#fff",
        backgroundColor: "#2196f3",
      });

      $("#loader").hide();
      setInterval("location.reload()", 3000);
    },
  });
});


let specialKeys = new Array();
specialKeys.push(8, 46); //Backspace
function IsNumeric(e) {
  let keyCode = e.which ? e.which : e.keyCode;
  console.log(keyCode);
  let ret =
    (keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1;
  return ret;
}

