$(document).ready(function () {
    // Run the plugin to initialize the back-to-top button
    $("body").toTopButton({});
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
});