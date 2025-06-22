window["onAjaxSuccess"] = () => {
    $("#crud_modal").modal("hide");
    $("#questionModal").modal("hide");
    $("#questionHomeworkModal").modal("hide");
    $("#videoModal").modal("hide");
    $("#crud_homework").modal("hide");
    $("#crud_modal").modal("hide");
    if (typeof datatable !== "undefined") datatable.draw();
    if (typeof vid_datatable !== "undefined") vid_datatable.draw();
    if (typeof qz_datatable !== "undefined") qz_datatable.draw();
    if (typeof homeworkdatatable !== "undefined") homeworkdatatable.draw();
};

window["onAjaxError"] = (status, response) => {
    $(".restore-item").on("click", function (e) {
        e.preventDefault();
        UIBlocker.block();

        $.ajax({
            type: "get",
            url: $(this).attr("href"),
            success: function (data) {
                datatable.draw();
                $("#crud_modal").modal("hide");
                showToast(__("Item has been restored successfully"));
                removeValidationMessages();

                UIBlocker.release();
            },
        });
    });
};
