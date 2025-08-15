
/** End :: System Alerts  **/

let initTinyMc = function (editingInp = false, height = 500) {
    tinymce.init({
        selector: ".tinymce",
        height: height,
        menubar: true,
        plugins: [
            "advlist autolink link image lists charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table paste help wordcount",
            "codesample directionality",
        ],
        toolbar: [
            "undo redo | formatselect fontselect fontsizeselect | bold italic underline strikethrough forecolor backcolor",
            "alignleft aligncenter alignright alignjustify | outdent indent numlist bullist | ltr rtl",
            "link unlink anchor image media table charmap emoticons | codesample code",
            "removeformat preview fullscreen print",
        ],
        link_context_toolbar: true,
        default_link_target: "_blank",
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        directionality: language, // RTL or LTR
        branding: false,
        image_advtab: true,
        automatic_uploads: true,
        file_picker_types: "file image media",
        save_onsavecallback: function () {
            console.log("Saved content!");
        },
    });

    if (!editingInp) $(".tinymce").val(null);
};
 
/** Start :: Submit any form in dashboard function  **/
let submitForm = (form) => {
    let submitBtn = $(form).find("[type=submit]");

    submitBtn.attr("disabled", true).attr("data-kt-indicator", "on");

    saveTinyMceDataIntoTextArea();

    ajaxSubmission({
        form: form,
        successCallback: function (response) {
            form = $(form);
            removeValidationMessages();

            if (form.data("success-callback") !== undefined) {
                window[form.data("success-callback")](response);
                showToast();
            } else {
                if (
                    form.data("redirection-url") &&
                    form.data("redirection-url") !== "#"
                ) {
                    showToast();
                    window.location.replace(form.data("redirection-url"));
                } else {
                    showToast();
                }
            }
        },
        errorCallback: function (response) {
            form = $(form);

            removeValidationMessages();

            if (response.status === 422)
                displayValidationMessages(response.responseJSON.errors, form);
            else if (response.status === 403) unauthorizedAlert();
            else if (response.status === 419) window.location.reload();
            else errorAlert(response.responseJSON.message, 5000);

            if (form.data("error-callback") !== undefined)
                window[form.data("error-callback")](response.status, response);
        },
        complete: function () {
            submitBtn.attr("disabled", false).removeAttr("data-kt-indicator");
        },
    });
};
/** End   :: Submit any form in dashboard function  **/

let showToast = function (message = null) {
    const toastElement = document.getElementById("kt_docs_toast_toggle");
    const toast = bootstrap.Toast.getOrCreateInstance(toastElement);
    if (message) $(".toast-body").text(message);
    toast.show();

    playSuccessSound();
};

function playNotificationSound() {
    if (notificationSoundOn) playSound($("#notification-sound"));
}

function playSuccessSound() {
    playSound($("#success-sound"));
}

function playErrorSound() {
    playSound($("#error-sound"));
}

function playSound(soundElement) {
    if (soundStatus != "stop") {
        try {
            soundElement.trigger("play");
        } catch (error) {
            console.log(error);
        }
    }
}

var reinitializeTooltip = () => {
    const tooltipTriggerList = document.querySelectorAll(
        '[data-bs-toggle="tooltip"]'
    );
    const tooltipList = [...tooltipTriggerList].map(
        (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
    );
};

var hideValidationMessagesOnModalShow = () => {
    $("#crud_modal").on("hidden.bs.modal", function (e) {
        removeValidationMessages();
    });
};

$(document).ready(function () {
    hideValidationMessagesOnModalShow();

    /** Start :: ajax request form  **/
    $(".ajax-form").submit(function (event) {
        event.preventDefault();

        submitForm(this);
    });
    /** End   :: ajax request form  **/

    $(".datepicker").flatpickr({
        dateFormat: "Y-m-d",
        locale: locale,
    });

    $(".timepicker").flatpickr({
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        locale: locale,
    });
});
function getRandomColorCode() {
    // Generate random values for red, green, and blue
    const red = Math.floor(Math.random() * 256);
    const green = Math.floor(Math.random() * 256);
    const blue = Math.floor(Math.random() * 256);

    // Create the color code using RGB values
    const colorCode = `rgb(${red}, ${green}, ${blue})`;

    return colorCode;
}

$(".multiselectsplitter").multiselectsplitter();
