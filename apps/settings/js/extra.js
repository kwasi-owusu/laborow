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
  