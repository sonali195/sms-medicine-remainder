jQuery(document).ready(function($) {
    var medicineCount = $('.medicine-group').length || 1;

    // Inject CSS styles dynamically
    var styles = `
    <style>
        button.increment-button, button.increment-button1 {
            background: #000 !important;
            margin: 0px !important;
            border-radius: 50% !important;
            padding: 3px !important;
            height: 30px;
            width: 30px;
            position: absolute;
            right: 15px;
        }
        button.decrement-button, button.decrement-button1 {
            background: none !important;
            color: #000 !important;
            padding: 0px !important;
            margin: 0px !important;
            position: absolute;
            left: 15px;
        }
        input.dose-value, input.duration-value {
            border: none !important;
            margin-bottom: 0px !important;
            text-align: center;
        }
    </style>`;
    $('head').append(styles);

    function toggleRemoveButtons() {
        if (medicineCount > 1) {
            $('.remove-medicine').show();
        } else {
            $('.remove-medicine').hide();
        }
    }

    function bindIncrementDecrementButtons(index) {
        var doseIncrementButton = $(`#medicine-group-${index} .dose-increment-button`);
        var doseDecrementButton = $(`#medicine-group-${index} .dose-decrement-button`);
        var doseInputField = $(`#medicine-group-${index} .dose-value`);

        var durationIncrementButton = $(`#medicine-group-${index} .duration-increment-button`);
        var durationDecrementButton = $(`#medicine-group-${index} .duration-decrement-button`);
        var durationInputField = $(`#medicine-group-${index} .duration-value`);

        doseIncrementButton.on('click', function() {
            doseInputField[0].stepUp();
        });

        doseDecrementButton.on('click', function() {
            doseInputField[0].stepDown();
        });

        durationIncrementButton.on('click', function() {
            durationInputField[0].stepUp();
        });

        durationDecrementButton.on('click', function() {
            durationInputField[0].stepDown();
        });
    }

    // Function to add new medicine group
    function addNewMedicineGroup() {
        medicineCount++;

        var newMedicineField = `
            <div class="medicine-group" id="medicine-group-${medicineCount}">
                <div class="row">
                    <div class="col-md-8">
                        <label for="medicine_name_${medicineCount}">Medicine Name</label>
                        <input class="form-control" type="text" name="medicine_name[]" id="medicine_name_${medicineCount}" required><br>
                        <span id="medicine_name_${medicineCount}_error" class="error"></span>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="medicine_name_${medicineCount}"></label>
                        <button style="width: 163px;
    
    flex-grow: 0;
    font-family: Montserrat;
    font-size: 16px;
    font-weight: 600;
    font-stretch: normal;
    font-style: normal;
    line-height: 1.5;
    letter-spacing: normal;
    text-align: left;
    color: #fff !important;
    padding: 10px 0px 10px 0px;
    height: auto;
    text-align: center;
" type="button" class="remove-medicine" data-id="${medicineCount}">Remove<i class="fa-solid fa-minus"></i></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label for="dose_type_${medicineCount}">Set Dose</label>
                        <select class="form-control" name="dose_type[]" id="dose_type_${medicineCount}" required>
                            <option value="spoon">Spoon</option>
                            <option value="ml">Milliliter</option>
                            <option value="mm">Millimeter</option>
                            <option value="number">Number</option>
                        </select>
                        <span id="dose_type_${medicineCount}_error" class="error"></span>
                    </div>
                    <div class="col-md-3">
                        <label for="">Value</label>
                        <div class="d-flex flex-wrap justify-content-center align-items-center" style="gap: 10px;border:1px solid #ddd; border-radius:10px; position:relative;">
                            <button type="button" class="dose-decrement-button decrement-button">-</button>
                            <input class="form-control w-25 dose-value" type="number" name="dose_value[]" id="dose_value_${medicineCount}" value="0" min="0" required>
                            <button type="button" class="dose-increment-button increment-button">+</button>
                        </div>
                        <span id="dose_value_${medicineCount}_error" class="error"></span>
                    </div>
                    <div class="col-md-6">
                        <label for="frequency_${medicineCount}">Add Frequency</label>
                        <select class="form-control" name="frequency[]" id="frequency_${medicineCount}" required>
                            <option value="select">Select</option>
                            <option value="daily">Just once</option>
                            <option value="onceaday">Once a day</option>
                            <option value="atnight">At night</option>
                            <option value="every4hrs">Every 4 hrs</option>
                            <option value="every6hrs">Every 6 hrs</option>
                            <option value="every8hrs">Every 8 hrs</option>
                            <option value="every12hrs">Every 12 hrs</option>
                        </select>
                        <span id="frequency_${medicineCount}_error" class="error"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label for="duration_type_${medicineCount}">Set Duration</label>
                        <select class="form-control" name="duration_type[]" id="duration_type_${medicineCount}" required>
                            <option value="6-Month">6 Month</option>
                            <option value="1-Year">1 Year</option>
                            <option value="Life-time">Life time</option>
                        </select>
                        <span id="duration_type_${medicineCount}_error" class="error"></span>
                    </div>
                    <div class="col-md-3">
                        <label for="">Value</label>
                        <div class="d-flex flex-wrap justify-content-center align-items-center" style="gap: 10px;border:1px solid #ddd; border-radius:10px; position:relative;">
                            <button type="button" class="duration-decrement-button decrement-button1">-</button>
                            <input class="form-control w-25 duration-value" type="number" name="duration_value[]" id="duration_value_${medicineCount}" value="0" min="0" required>
                            <button type="button" class="duration-increment-button increment-button1">+</button>
                        </div>
                        <span id="duration_value_${medicineCount}_error" class="error"></span>
                    </div>
                    <div class="col-md-3">
                        <label for="instruction_${medicineCount}">Medicine Instruction</label>
                        <select class="form-control" name="instruction[]" id="instruction_${medicineCount}" required>
                            <option value="beforefood">Before food</option>
                            <option value="withfood">With food</option>
                            <option value="afterfood">After food</option>
                        </select><br>
                        <span id="instruction_${medicineCount}_error" class="error"></span>
                    </div>
                    <div class="col-md-3">
                        <label for="hour_time_${medicineCount}">Hours/Min</label>
                        <input style="border:1px solid #ddd;" class="form-control hoursminte" type="time" name="hour_time[]" id="hour_time_${medicineCount}" required>
                        <span id="hour_time_${medicineCount}_error" class="error"></span>
                    </div>
                </div>
            </div>
        `;

        $('#medicine-fields').append(newMedicineField);
        toggleRemoveButtons();
        bindIncrementDecrementButtons(medicineCount);  // Bind buttons for the new medicine group
    }

    // Event handler for adding more medicine
    $('#add-more-medicine').on('click', function() {
        addNewMedicineGroup();
    });

    // Event handler for removing medicine group
    $(document).on('click', '.remove-medicine', function() {
        var medicineId = $(this).data('id');
        $('#medicine-group-' + medicineId).remove();
        medicineCount--;
        toggleRemoveButtons();
    });

    // Initial call to hide the remove button
    toggleRemoveButtons();

    // Bind increment and decrement buttons for existing fields
    for (let i = 1; i <= medicineCount; i++) {
        bindIncrementDecrementButtons(i);
    }

    // Form submission validation
    $('#pill-notifications-form').on('submit', function(event) {
        let valid = true;

        // Title Reminder Validation
        const titleReminder = document.getElementById('title_reminder');
        const titleReminderError = document.getElementById('title_reminder_error');
        if (!titleReminder.value.trim()) {
            titleReminderError.textContent = 'Title Reminder is required.';
            valid = false;
        } else {
            titleReminderError.textContent = '';
        }

        $('.medicine-group').each(function(index, group) {
            const medicineName = group.querySelector(`#medicine_name_${index + 1}`);
            const medicineNameError = group.querySelector(`#medicine_name_${index + 1}_error`);
            if (!medicineName.value.trim()) {
                medicineNameError.textContent = 'Medicine Name is required.';
                valid = false;
            } else {
                medicineNameError.textContent = '';
            }

            const doseType = group.querySelector(`#dose_type_${index + 1}`);
            const doseTypeError = group.querySelector(`#dose_type_${index + 1}_error`);
            if (!doseType.value) {
                doseTypeError.textContent = 'Dose Type is required.';
                valid = false;
            } else {
                doseTypeError.textContent = '';
            }

            const doseValue = group.querySelector(`#dose_value_${index + 1}`);
            const doseValueError = group.querySelector(`#dose_value_${index + 1}_error`);
            if (!doseValue.value) {
                doseValueError.textContent = 'Dose Value is required.';
                valid = false;
            } else {
                doseValueError.textContent = '';
            }

            const durationType = group.querySelector(`#duration_type_${index + 1}`);
            const durationTypeError = group.querySelector(`#duration_type_${index + 1}_error`);
            if (!durationType.value) {
                durationTypeError.textContent = 'Duration Type is required.';
                valid = false;
            } else {
                durationTypeError.textContent = '';
            }

            const durationValue = group.querySelector(`#duration_value_${index + 1}`);
            const durationValueError = group.querySelector(`#duration_value_${index + 1}_error`);
            if (!durationValue.value) {
                durationValueError.textContent = 'Duration Value is required.';
                valid = false;
            } else {
                durationValueError.textContent = '';
            }

            const frequency = group.querySelector(`#frequency_${index + 1}`);
            const frequencyError = group.querySelector(`#frequency_${index + 1}_error`);
            if (!frequency.value) {
                frequencyError.textContent = 'Frequency is required.';
                valid = false;
            } else {
                frequencyError.textContent = '';
            }

            const instruction = group.querySelector(`#instruction_${index + 1}`);
            const instructionError = group.querySelector(`#instruction_${index + 1}_error`);
            if (!instruction.value) {
                instructionError.textContent = 'Instruction is required.';
                valid = false;
            } else {
                instructionError.textContent = '';
            }

            const hourTime = group.querySelector(`#hour_time_${index + 1}`);
            const hourTimeError = group.querySelector(`#hour_time_${index + 1}_error`);
            if (!hourTime.value) {
                hourTimeError.textContent = 'Time is required.';
                valid = false;
            } else {
                hourTimeError.textContent = '';
            }
        });

        if (!valid) {
            event.preventDefault();
        }
    });
});
