window["onAjaxSuccess"] = () => {
    $("#crud_modal").modal("hide");
    datatable.draw();
    $("#questionModal").modal("hide");
    datatable.draw();
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
