let specialKeys = new Array();
specialKeys.push(8, 46); //Backspace
function IsNumeric(e) {
  let keyCode = e.which ? e.which : e.keyCode;
  console.log(keyCode);
  let ret =
    (keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1;
  return ret;
}

$(document).on("change", "#asset_category", function () {
  let asset_category_id = $(this).val();

  $.ajax({
    url: "apps/listing/controller/CTRGetSubCategoryByID.php",
    type: "POST",
    //dataType:"json",
    data: { asset_category_id: asset_category_id },
    success: function (data) {
      $("#sub_cat_here").html(data);
    },
  });
});

$(document).on("change", "#asset_category_b", function () {
  let asset_category_id = $(this).val();

  $.ajax({
    url: "apps/listing/controller/CTRGetSubCategoryByID.php",
    type: "POST",
    //dataType:"json",
    data: { asset_category_id: asset_category_id },
    success: function (data) {
      $("#sub_cat_here_b").html(data);
    },
  });
});


$("#list_asset_frm").on("submit", function (e) {
  $("#saveBtn").prop("disabled", true);
  $("#list_loader").show();
  e.preventDefault();

  $.ajax({
    url: "apps/listing/controller/SaveThisListing.php",
    method: "POST",
    data: new FormData(this),
    contentType: false,
    //dataType: "json",
    cache: false,
    processData: false,
    success: function (data) {
      $.toast({
        heading: "Laborow",
        text: data,
        icon: "info",
        loader: true, // Change it to false to disable loader
        position: "top-right",
        loaderBg: "#E31D1D", // To change the background
      });

      $("#list_loader").hide();

      setInterval("location.reload()", 3000);
    },
  });
});

$(document).ready(function () {
  $("#loader").on("inview", function (event, isInView) {
    if (isInView) {
      let nxtpg = parseInt($("#pageno").val()) + 1;
      let asset_category_ID = $("#asset_category_ID").val();
      //let prop_sub_type = $("#prop_sub_type").val();

      //$('#checkWhat').val(prop_sub_type);
      $.ajax({
        type: "POST",
        url: "apps/listing/controller/CTRLoadMoreListings.php",
        data: {
          nxtpg: nxtpg,
          listing_category: asset_category_ID,
        },
        success: function (data) {
          if (data != " " && data != "") {
            $("#rspnd").append(data);
            $("#pageno").val(nxtpg);
          } else {
            $("#loader").hide();
          }
        },
      });
    }
  });
});

function bookmarkThis(itm) {
  let id = $(itm).attr("data-id");
  let isLogin = $("#check_is_login").val();
  //$('#chk_lgn').text(isLogin);

  if (isLogin == 1) {
    $.ajax({
      type: "POST",
      url: "apps/listing/controller/AddBookmarkController.php",
      data: { id: id },
      success: function (data) {
        $.toast({
          heading: "Renters Paradise",
          text: data,
          icon: "info",
          loader: true, // Change it to false to disable loader
          position: "top-right",
          loaderBg: "#9EC600", // To change the background
        });
        //$('#chk_lgn').html(data);
      },
    });
  } else {
    $.toast({
      heading: "Laborow",
      text: "You need to login to add a bookmark",
      icon: "info",
      loader: true, // Change it to false to disable loader
      position: "top-right",
      loaderBg: "#9EC600", // To change the background
    });
    //$('#chk_lgn').html("You need to login to add a bookmark");
  }
}

// CKEDITOR.replace('asset_desc',{
//   height: "200px"
// });

// $('#asset_description_b').summernote({
//   tabsize: 5,
//   height: '250',
//   name: 'asset_desc',
//   toolbar: [
//     ['style', ['style']],
//     ['font', ['bold', 'underline', 'clear']],
//     ['color', ['color']],
//     ['para', ['ul', 'ol', 'paragraph']],
//     ['table', ['table']],
//     ['insert', ['link', 'picture', 'video']],
//     ['view', ['fullscreen', 'codeview', 'help']]
//   ]
// });

// function quickView(itm) {

//   let asset_id          = $(itm).attr("data-id");;
//   let asset_title       = $(itm).attr("data-title");
//   let rent_amount       = $(itm).attr("data-amount");
//   let asset_description = $(itm).attr("data-desc");
//   let asset_slug        = $(itm).attr("data-slug");

//   let dd = {
//     asset_id : asset_id,
//     asset_title: asset_title,
//     rent_amount: rent_amount,
//     asset_description: asset_description,
//     asset_slug: asset_slug
//   }

//   let paramsThis = $.param(dd)
  
//   $('<div>').load('apps/listing/view/modal/do.quick_view_modal.php?' + paramsThis, function (data) {
//       $("#load_quick_view_modal_here").html(data);
//   });
// }