

// Description: Where store all js include ajax to get active campus, age validation, phone int with nation code, day and time selector, preview Image, multi select dropdown daysList, summernote, datatables, change status use AJAX, and other option for target audience.
//  Author: An Bao Le


// ajax to get active campus
function fetchCampuses(courseId) {
    var $campusSelect = $('#campus_id');
    if (!courseId) {
        $campusSelect.html('<option value="">Select Course First</option>').prop('disabled', true);
        return;
    }
    $.ajax({
        url: 'get_active_campuses.php',
        type: 'GET',
        data: {
            course_id: courseId
        },
        dataType: 'json',
        success: function (campuses) {
            $campusSelect.empty();
            if (campuses.length > 0) {
                $campusSelect.append('<option value="">Select Campus</option>');
                $.each(campuses, function (index, campus) {
                    $campusSelect.append($('<option></option>').val(campus.campus_id).text(campus.name));
                });
                $campusSelect.prop('disabled', false);
            } else {
                $campusSelect.append('<option value="">No active campus available</option>').prop('disabled', true);
            }
        }
    });
}
$('#course_id').on('change', function () {
    fetchCampuses($(this).val());
    validateAge();
    // check age if change course
});
var initialCourseId = $('#course_id').val();
if (initialCourseId) {
    fetchCampuses(initialCourseId);
}
// ---js check age---
function calculateAge(dobString) {
    var dob = new Date(dobString);
    var today = new Date();
    var age = today.getFullYear() - dob.getFullYear();
    var m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    return age;
}
function validateAge() {
    var dobVal = $('#date_of_birth').val();
    var $selectedOption = $('#course_id').find(':selected');
    var minAge = $selectedOption.data('min');
    var maxAge = $selectedOption.data('max');
    var groupName = $selectedOption.data('group') || '';
    var $dobInput = $('#date_of_birth');
    var $errorFeedback = $('#dob_feedback');
    var $validFeedback = $('#dob_valid_feedback');
    var $submitBtn = $('#submit_btn');
    // reset default
    $dobInput.removeClass('is-invalid is-valid');
    $errorFeedback.hide().text('');
    $validFeedback.hide().text('');
    $submitBtn.prop('disabled', false);
    if (!dobVal || !$selectedOption.val()) return;
    var userAge = calculateAge(dobVal);
    // do age condition
    var isValid = true;
    var errorMsg = "";
    if (minAge !== "" && minAge !== null && userAge < minAge) {
        isValid = false;
        errorMsg = "✖ Required age: Minimum " + minAge + " years old. (Your age: " + userAge + ")";
    } else if (maxAge !== "" && maxAge !== null && userAge > maxAge) {
        isValid = false;
        errorMsg = "✖ Required age: Maximum " + maxAge + " years old. (Your age: " + userAge + ")";
    }
    if (!isValid) {
        $dobInput.addClass('is-invalid');
        $errorFeedback.html(errorMsg).show();
        $submitBtn.prop('disabled', true); // lock submit button when don't pass
    } else {
        $dobInput.addClass('is-valid');
        var passMsg = "✔ Eligible (" + userAge + " years old)";
        if (groupName) passMsg += " - Target: " + groupName;
        $validFeedback.html(passMsg).show();
        $submitBtn.prop('disabled', false);
    }
}
//do when user choose dob
$('#date_of_birth').on('change input', function () {
    validateAge();
});
// phone
$(document).ready(function () {
    const phoneInput = document.querySelector("#phone");
    if (phoneInput) {
        window.intlTelInput(phoneInput, {
            initialCountry: "nz",
            separateDialCode: true,
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@29.0.5/dist/js/utils.js"
        });
    }
});
// Dynamic Day & Time Selector
const daysList = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

function updateDayOptions() {
    let selectedDays = [];
    $(".day-select").each(function () {
        let val = $(this).val();
        if (val) selectedDays.push(val);
    });

    $(".day-select").each(function () {
        let currentVal = $(this).val();
        $(this).find("option").each(function () {
            let optVal = $(this).val();
            if (optVal && optVal !== currentVal && selectedDays.includes(optVal)) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    });
    // will hide the day if check this day already
    if (selectedDays.length >= 7) {
        $("#addDayBtn").hide();
    } else {
        $("#addDayBtn").show();
    }

    if ($(".availability-row").length > 1) {
        $(".remove-day-btn").show();
    } else {
        $(".remove-day-btn").hide();
    }
}

$("#addDayBtn").on("click", function () {
    let rowCount = $(".availability-row").length;
    if (rowCount < 7) {
        let newRow = `
        <div class="row g-2 align-items-center mb-2 availability-row">
            <div class="col-md-4">
                <select name="avail_day[]" class="form-select border-2 day-select" required>
                    <option value="">Select Day</option>
                    ${daysList.map(d => `<option value="${d}">${d}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-white border-2">From</span>
                    <input type="time" name="avail_start[]" class="form-control border-2" required>
                    <span class="input-group-text bg-white border-2">To</span>
                    <input type="time" name="avail_end[]" class="form-control border-2" required>
                </div>
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-outline-danger w-100 remove-day-btn">
                    &times;
                </button>
            </div>
        </div>`;

        $("#availabilityContainer").append(newRow);
        updateDayOptions();
    }
});

$(document).on("change", ".day-select", function () {
    updateDayOptions();
});

$(document).on("click", ".remove-day-btn", function () {
    $(this).closest(".availability-row").remove();
    updateDayOptions();
});
// PREVIEW IMAGE
$(function () {
    $('#file-input').imoViewer({
        'preview': '#image-previewer',
        'maxWidth': 9999,
        'maxHeight': 9999
    });
});
// MULTI
$(document).ready(function () {
    $('#skills').interActiveMultiSelect({
        mode: 'checkbox',
        placeholder: 'Select',
        search: false,
        searchPlaceholder: 'Search...',
        noResultsText: 'No results found',
        selectAllText: 'Select All',
        clearText: 'Clear'
    });
});
//SUMMERNTOE
$(document).ready(function () {
    $('#articleEditor').summernote({
        placeholder: 'Write the course description here...',
        tabsize: 2,
        height: 300,
        toolbar: [
            ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });
});
//DATA TABLES
$(document).ready(function () {
    if ($('.admin-datatable').length > 0) {
        $('.admin-datatable').DataTable({
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 10,
            "order": [],
            "language": {
                "lengthMenu": "Show _MENU_ entries per page",
                "zeroRecords": "No matching records found",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total records)",
                "search": "Search:",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });
    }
});
// admin contact message
$(document).ready(function () {
    const badgeMap = {
        'Pending': 'bg-warning text-dark',
        'Replied': 'bg-success text-white'
    };

    $('.status-select').on('change', function () {
        let selectElem = $(this);
        let contactId = selectElem.data('id');
        let newStatus = selectElem.val();

        // change color
        selectElem.removeClass('bg-warning bg-success text-dark text-white');
        selectElem.addClass(badgeMap[newStatus]);

        // send ajax
        $.ajax({
            url: 'contact_messages.php',
            type: 'POST',
            data: {
                action: 'update_status',
                contact_id: contactId,
                status: newStatus
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#ajaxAlertContainer').html('<div class="alert alert-success text-start">' + response.message + '</div>');
                } else {
                    $('#ajaxAlertContainer').html('<div class="alert alert-danger text-start">' + response.message + '</div>');
                }
            },
            error: function (xhr) {
                console.log("Error Response:", xhr.responseText);
                $('#ajaxAlertContainer').html('<div class="alert alert-danger text-start">Server Connection Error!</div>');
            }
        });
    });
});
//admin campuses
$(document).ready(function () {
    const badgeMap = {
        'active': 'bg-success text-white',
        'inactive': 'bg-danger text-white'
    };

    $('.campus-status-select').on('change', function () {
        let selectElem = $(this);
        let campusId = selectElem.data('id');
        let newStatus = selectElem.val();

        if (newStatus === 'inactive') {
            if (!confirm('Warning: Setting this campus to INACTIVE will also disable all linked courses at this location. Continue?')) {
                selectElem.val('active');
                return;
            }
        }

        selectElem.removeClass('bg-success bg-danger text-white');
        selectElem.addClass(badgeMap[newStatus]);

        $.ajax({
            url: 'campuses.php',
            type: 'POST',
            data: {
                action: 'update_campus_status',
                campus_id: campusId,
                status: newStatus
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#ajaxAlertContainer').html('<div class="alert alert-success text-start">' + response.message + '</div>');
                } else {
                    $('#ajaxAlertContainer').html('<div class="alert alert-danger text-start">' + response.message + '</div>');
                }
            },
            error: function (xhr) {
                console.log("Error Response:", xhr.responseText);
                $('#ajaxAlertContainer').html('<div class="alert alert-danger text-start">Server connection error!</div>');
            }
        });
    });
});
//admin add and edit course
$(document).ready(function () {
    // Handling Select Changes
    $(document).on('change', '.ta-select', function () {
        let row = $(this).closest('.ta-row');
        let selectedOpt = $(this).find('option:selected');
        let val = $(this).val();

        if (val === 'other') {
            row.find('.custom-ta-input').removeClass('d-none').prop('required', true);
            row.find('.min-age-input, .max-age-input').prop('readonly', false).val('');
        } else if (val !== '') {
            row.find('.custom-ta-input').addClass('d-none').prop('required', false).val('');
            row.find('.min-age-input').val(selectedOpt.data('min')).prop('readonly', true);
            row.find('.max-age-input').val(selectedOpt.data('max')).prop('readonly', true);
        } else {
            row.find('.custom-ta-input').addClass('d-none').prop('required', false).val('');
            row.find('.min-age-input, .max-age-input').val('').prop('readonly', true);
        }
    });

    // Add New Target Audience Row
    $('#addTaBtn').on('click', function () {
        let firstRow = $('.ta-row').first().clone();

        firstRow.find('select').val('');
        firstRow.find('input').val('');
        firstRow.find('.custom-ta-input').addClass('d-none').prop('required', false);
        firstRow.find('.min-age-input, .max-age-input').prop('readonly', true);

        $('#targetAudienceContainer').append(firstRow);
        updateRemoveButtons();
    });

    // Remove Row
    $(document).on('click', '.remove-ta-btn', function () {
        $(this).closest('.ta-row').remove();
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        if ($('.ta-row').length > 1) {
            $('.remove-ta-btn').show();
        } else {
            $('.remove-ta-btn').hide();
        }
    }

    $('#file-input').on('change', function (e) {
        let file = e.target.files[0];
        if (file) {
            let validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            let maxSize = 5 * 1024 * 1024; // 5MB

            if (!validTypes.includes(file.type)) {
                alert('Lỗi: Chỉ chấp nhận các định dạng hình ảnh (JPG, PNG, WEBP, GIF)!');
                $(this).val('');
                $('#image-previewer').attr('src', '../image/profile.png');
                return;
            }

            if (file.size > maxSize) {
                alert('Lỗi: Kích thước hình ảnh không được vượt quá 5MB!');
                $(this).val('');
                $('#image-previewer').attr('src', '../image/profile.png');
                return;
            }

            let reader = new FileReader();
            reader.onload = function (event) {
                $('#image-previewer').attr('src', event.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
});
//admin enquiries
$(document).ready(function () {
    const badgeMap = {
        'Pending': 'bg-warning text-dark',
        'Contacted': 'bg-info text-dark',
        'Enrolled': 'bg-success text-white',
        'Cancelled': 'bg-danger text-white'
    };

    $('.status-select').on('change', function () {
        let selectElem = $(this);
        let enquiryId = selectElem.data('id');
        let newStatus = selectElem.val();

        // change color
        selectElem.removeClass('bg-warning bg-info bg-success bg-danger bg-secondary text-dark text-white');
        selectElem.addClass(badgeMap[newStatus] || 'bg-secondary text-white');

        // send ajax
        $.ajax({
            url: 'enquiries.php',
            type: 'POST',
            data: {
                action: 'update_status',
                enquiry_id: enquiryId,
                status: newStatus
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#ajaxAlertContainer').html('<div class="alert alert-success text-start">' + response.message + '</div>');
                } else {
                    $('#ajaxAlertContainer').html('<div class="alert alert-danger text-start">' + response.message + '</div>');
                }
            },
            error: function (xhr, status, error) {
                console.log("Response text:", xhr.responseText); // Show in F12
                $('#ajaxAlertContainer').html('<div class="alert alert-danger text-start">Server connection error or response error!</div>');
            }
        });
    });
});