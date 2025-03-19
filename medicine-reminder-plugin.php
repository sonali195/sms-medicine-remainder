<?php 
/*
Plugin Name: Pill Notifications
Description: A WordPress plugin to create and manage pill notifications.
Version: 1.0
Author: Mopheth
*/

class PillNotifications {
    public function __construct() {
        // Constructor remains the same
        register_activation_hook(__FILE__, [$this, 'create_database_table']);
        add_action('admin_post_save_pill_notification', [$this, 'save_form_data']);
        add_action('admin_post_nopriv_save_pill_notification', [$this, 'save_form_data']);
        add_shortcode('pill_notifications_form', [$this, 'render_form']);
        add_shortcode('pill_notifications_results', [$this, 'display_form_results']);
        add_shortcode('medicine_listing', [$this, 'display_medicine_listing']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_post_edit_pill_notification', [$this, 'edit_pill_notification']);
        add_action('admin_post_delete_pill_notification', [$this, 'delete_pill_notification']);
        add_shortcode('edit_pill_reminder_form', [$this, 'edit_pill_reminder_form']);
    }


    public function create_database_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pill_notifications';
        $charset_collate = $wpdb->get_charset_collate();
    
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            title_reminder VARCHAR(255) NOT NULL,
            medicine_data TEXT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            reminder_time TIME NOT NULL,
            end_time TIME NOT NULL,
            mobile_num VARCHAR(20) NOT NULL,
            status TINYINT(1) DEFAULT 1 NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
        ) $charset_collate;";
    
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }


    public function edit_pill_notification() {
        if (isset($_GET['entry_id']) && is_user_logged_in()) {
            $entry_id = intval($_GET['entry_id']);
            wp_redirect(home_url('/edit-pill-reminder/?entry_id=' . $entry_id)); // Redirect to a new edit page URL
            exit;
        } else {
            wp_die('You do not have sufficient permissions to access this page.');
        }
    }


    
public function delete_pill_notification() {
    if (isset($_GET['entry_id']) && is_user_logged_in()) {
        $entry_id = intval($_GET['entry_id']);
        global $wpdb;
        $table_name = $wpdb->prefix . 'pill_notifications';
        
        // Delete the entry from the database
        $wpdb->delete($table_name, array('id' => $entry_id), array('%d'));
        
        // Redirect back to the listing page
        wp_redirect(home_url('/pill-reminders/'));// Adjust this URL as per your actual page URL
        exit;
    } else {
        wp_die('You do not have sufficient permissions to access this page.');
    }
}

public function render_form() {
		    // Check if user is logged in
    if (!is_user_logged_in()) {
        return '<p class="loginmessage">You need to be logged in to access this form here <a href="https://mophethonline.com/login/">Login</a>.</p>';
    }
        ob_start();
    global $wpdb;
    $entry = null;
    $user_id = get_current_user_id(); // Get the current user ID

    $current_date = date('d-m-Y');

    if (isset($_GET['entry_id'])) {
        $entry_id = intval($_GET['entry_id']);
        $table_name = $wpdb->prefix . 'pill_notifications';
        $entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d AND user_id = %d", $entry_id, $user_id), ARRAY_A);
        if (!$entry) {
            return '<p>No entry found for editing.</p>';
        }
    }
        ?>
       <style>
            #pill-notifications-form {
                margin: auto;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 10px;
                background-color: #f9f9f9;
            }
            #pill-notifications-form label {
                display: block;
                margin-bottom: 8px;
                font-weight: bold;
            }
            #pill-notifications-form input[type="text"],
            #pill-notifications-form input[type="number"],
            #pill-notifications-form select {
                width: 100%;
                padding: 8px;
              
                border: 1px solid #ddd;
                border-radius: 5px;
                box-sizing: border-box;
            }
            #pill-notifications-form button,
            #pill-notifications-form input[type="submit"] {
                display: inline-block;
                padding: 10px 20px;
                margin-top: 10px;
                border: none;
                border-radius: 5px;
                background-color: #ff0000;
                color: #fff;
                cursor: pointer;
            }
            #pill-notifications-form button:hover,
            #pill-notifications-form input[type="submit"]:hover {
                background-color: #ff0000;
            }
            .medicine-group {
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 10px;
                margin-bottom: 20px;
                background-color: #fff;
            }
                        .remove-medicine {
            background-color: #ff0000;
            color: #fff;
            padding: 12px;
            border: 0;
            border-radius: 5px;
             }
            .remove-medicine:hover {
                background-color: #ff0000;
            }

            .form-results-table {
                width: 100%;
                border-collapse: collapse;
            }

            .form-results-grid {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }

            .result-entry {
                display: grid;
                grid-template-columns: 150px 1fr;
                gap: 10px;
                padding: 15px;
                border: 1px solid #ccc;
                border-radius: 5px;
                background-color: #f9f9f9;
            }

            .entry-label {
                font-weight: bold;
            }

            .entry-value {
                font-style: italic;
            }
            .Start-Date {
                width: 85px;
                height: 24px;
                flex-grow: 0;
                font-family: Montserrat;
                font-size: 16px;
                font-weight: 600;
                font-stretch: normal;
                font-style: normal;
                line-height: 1.5;
                letter-spacing: normal;
                text-align: left;
                color: #1a1a1a;
                }
                .error {
                color: red;
                font-size: 12px;
                display: block;
                margin-top: -10px;
                margin-bottom: 10px;
            }
            .Add-Medicine-information {
                width: 275px;
                height: 24px;
                flex-grow: 0;
                font-family: Montserrat;
                font-size: 17px;
                font-weight: bold;
                font-stretch: normal;
                font-style: normal;
                line-height: normal;
                letter-spacing: normal;
                text-align: left;
                color: #1a1a1a;
                }
                .Fill-below-info-to-add-reminder {
                width: 323px;
                height: 15px;
                flex-grow: 0;
                font-family: Montserrat;
                font-size: 17px;
                font-weight: bold;
                font-stretch: normal;
                font-style: normal;
                line-height: normal;
                letter-spacing: normal;
                text-align: left;
                color: #1a1a1a;
                }
                .add-more-medicine {
                width: 163px;
                height: 24px;
                flex-grow: 0;
                font-family: Montserrat;
                font-size: 16px;
                font-weight: 600;
                font-stretch: normal;
                font-style: normal;
                line-height: 1.5;
                letter-spacing: normal;
                text-align: left;
                color: #ff0000;
                }
                .Add-Reminder-Time {
                width: 164px;
                height: 24px;
                flex-grow: 0;
                font-family: Montserrat;
                font-size: 16px;
                font-weight: 500;
                font-stretch: normal;
                font-style: normal;
                line-height: 1.5;
                letter-spacing: normal;
                text-align: left;
                color: #000;
                }
                .Frame-1000002091 {
                height: 58px;
                flex-grow: 1;
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding: 17px 20px;
                border-radius: 8px;
                border: solid 0.5px #d9d9d9;
                background-color: #f9f9f9;
                }
                .Frame-1000002092 {
                height: 60px;
                flex-grow: 1;
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                align-items: stretch;
                gap: 10px;
                padding: 0;
                }
        .-\37482927871 {
            
                height: 24px;
                flex-grow: 0;
                font-family: Montserrat;
                font-size: 16px;
                font-weight: 600;
                font-stretch: normal;
                font-style: normal;
                line-height: 1.5;
                letter-spacing: normal;
                text-align: left;
                color: #1a1a1a;
                }

                            .dropbtn {
                    background: none !important;
                    color: black;
                    padding: 10px;
                    font-size: 16px;
                    border: none;
                    cursor: pointer;
                }
				   button#add-more-medicine {
			margin-top: 26px;
		}
		   button.remove-medicine {
              margin-top: 25px !important;
              }

                .dropbtn:hover, .dropbtn:focus {
                    background-color: #ddd;
                }

                .dropdown-content {
                    display: none;
                    position: absolute;
                    right: 0;
                    background-color: #f9f9f9;
                    min-width: 160px;
                    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
                    z-index: 1;
                }

                .dropdown-content a {
                    color: black;
                    padding: 12px 16px;
                    text-decoration: none;
                    display: block;
                }

                .dropdown-content a:hover {
                    background-color: #f1f1f1;
                }             
                </style>
                <style>
                .switch {
                position: relative;
                display: inline-block;
                width: 60px;
                height: 34px;
                }

                .switch input { 
                opacity: 0;
                width: 0;
                height: 0;
                }

                .slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                -webkit-transition: .4s;
                transition: .4s;
                }

                .slider:before {
                position: absolute;
                content: "";
                height: 26px;
                width: 26px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                -webkit-transition: .4s;
                transition: .4s;
                }

                input:checked + .slider {
                background-color: #2196F3;
                }

                input:focus + .slider {
                box-shadow: 0 0 1px #2196F3;
                }

                input:checked + .slider:before {
                -webkit-transform: translateX(26px);
                -ms-transform: translateX(26px);
                transform: translateX(26px);
                }

                /* Rounded sliders */
                .slider.round {
                border-radius: 34px;
                }

                .slider.round:before {
                border-radius: 50%;
                }
                p.loginmessage {
    background: #ffe8e8;
    border-radius: 5px;
    padding: 25px;
	font-weight:600;
	
}
                    </style>
    <head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    </head>
<form action="<?php echo esc_url(admin_url('admin-post.php?action=save_pill_notification')); ?>" method="POST" id="pill-notifications-form">
    <div class="Frame-1000002092">
        <span class="Fill-below-info-to-add-reminder"> Fill below info to add reminder</span>
    </div>
    <?php wp_nonce_field('save_pill_notification_nonce', 'pill_notification_nonce'); ?>
    <?php if ($entry) : ?>
        <input type="hidden" name="entry_id" value="<?php echo esc_attr($entry['id']); ?>">
    <?php endif; ?>
	 <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>"> <!-- Hidden field for user ID -->
    <label for="title_reminder">Title For Reminder</label>
    <input type="text" name="title_reminder" class="form-control" id="title_reminder" value="<?php echo esc_attr($entry['title_reminder'] ?? ''); ?>" required><br>
    <span id="title_reminder_error" class="error"></span>

    <div id="medicine-fields">
        <div class="Frame-1000002092">
            <span class="Fill-below-info-to-add-reminder"> Add Medicine information</span>
        </div>
        <?php
        $medicine_data = json_decode($entry['medicine_data'] ?? '[]', true);
        if (is_array($medicine_data) && !empty($medicine_data)) {
            foreach ($medicine_data as $index => $medicine) {
        ?>
    <div class="medicine-group" id="medicine-group-<?php echo $index + 1; ?>">
        <div class="row">
            <div class="col-md-6">
                <label for="medicine_name_<?php echo $index + 1; ?>">Medicine Name</label>
                <input type="text" name="medicine_name[]" class="form-control" id="medicine_name_<?php echo $index + 1; ?>" value="<?php echo esc_attr($medicine['medicine_name']); ?>" required><br>
                <span id="medicine_name_<?php echo $index + 1; ?>_error" class="error"></span>
            </div>
            <div class="col-md-6">
                <label for="add_more_<?php echo $index + 1; ?>"></label>
                <button type="button" id="add-more-medicine">Add more <i class="fa-solid fa-plus"></i></button>
            </div>
        </div>
                    <div class="row">
                        <div class="col-md-3 mt-2">
                            <label for="dose_type_<?php echo $index + 1; ?>">Set Dose</label>
                            <select class="form-control" name="dose_type[]" id="dose_type_<?php echo $index + 1; ?>" required>
                                <option value="spoon" <?php selected($medicine['dose_type'], 'spoon'); ?>>Spoon</option>
                                <option value="ml" <?php selected($medicine['dose_type'], 'ml'); ?>>Milliliter</option>
                                <option value="mm" <?php selected($medicine['dose_type'], 'mm'); ?>>Millimeter</option>
                                <option value="number" <?php selected($medicine['dose_type'], 'number'); ?>>Number</option>
                            </select><br>
                        </div>
                        <div class="col-md-3 mt-2">
                            <label class="mt-2" for="">Value</label>
                            <span id="dose_type_<?php echo $index + 1; ?>_error" class="error"></span>
                            <input class="form-control" type="number" name="dose_value[]" id="dose_value_<?php echo $index + 1; ?>" value="<?php echo esc_attr($medicine['dose_value']); ?>" min="0" required><br>
                            <span id="dose_value_<?php echo $index + 1; ?>_error" class="error"></span>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label for="frequency_<?php echo $index + 1; ?>">Add Frequency</label>
                            <select name="frequency[]" id="frequency_<?php echo $index + 1; ?>" required>
                                <option value="select" <?php selected($medicine['frequency'], 'select'); ?>>Select</option>
                                <option value="daily" <?php selected($medicine['frequency'], 'daily'); ?>>Just once</option>
                                <option value="onceaday" <?php selected($medicine['frequency'], 'onceaday'); ?>>Once a day</option>
                                <option value="atnight" <?php selected($medicine['frequency'], 'atnight'); ?>>At night</option>
                                <option value="every4hrs" <?php selected($medicine['frequency'], 'every4hrs'); ?>>Every 4 hrs</option>
                                <option value="every6hrs" <?php selected($medicine['frequency'], 'every6hrs'); ?>>Every 6 hrs</option>
                                <option value="every8hrs" <?php selected($medicine['frequency'], 'every8hrs'); ?>>Every 8 hrs</option>
                                <option value="every12hrs" <?php selected($medicine['frequency'], 'every12hrs'); ?>>Every 12hrs</option>
                            </select><br>
                            <span id="frequency_<?php echo $index + 1; ?>_error" class="error"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="duration_type_<?php echo $index + 1; ?>">Set Duration</label>
                            <select name="duration_type[]" id="duration_type_<?php echo $index + 1; ?>" required>
                                <option value="Day" <?php selected($medicine['duration_type'], 'Day'); ?>>Day</option>
                                <option value="Month" <?php selected($medicine['duration_type'], 'Month'); ?>>Month</option>
                                <option value="Year" <?php selected($medicine['duration_type'], 'Year'); ?>>Year</option>
                                <option value="Life-time" <?php selected($medicine['duration_type'], 'Life-time'); ?>>Life time</option>
                            </select><br>
                        </div>
                        <div class="col-md-3">
                            <label class="mt-2" for="">Value</label>
                            <span id="duration_type_<?php echo $index + 1; ?>_error" class="error"></span>
                            <input type="number" name="duration_value[]" id="duration_value_<?php echo $index + 1; ?>" value="<?php echo esc_attr($medicine['duration_value']); ?>" min="0" required><br>
                            <span id="duration_value_<?php echo $index + 1; ?>_error" class="error"></span>
                        </div>
                        <div class="col-md-3">
                            <label for="instruction_<?php echo $index + 1; ?>">Medicine Instruction</label>
                            <select name="instruction[]" id="instruction_<?php echo $index + 1; ?>" required>
                                <option value="beforefood" <?php selected($medicine['instruction'], 'beforefood'); ?>>Before food</option>
                                <option value="withfood" <?php selected($medicine['instruction'], 'withfood'); ?>>With food</option>
                                <option value="afterfood" <?php selected($medicine['instruction'], 'afterfood'); ?>>After food</option>
                            </select><br>
                            <span id="instruction_<?php echo $index + 1; ?>_error" class="error"></span>
                        </div>
                        <div class="col-md-3">
                            <label for="time_type_1">Hours/Min</label>
                            <input style="border:1px solid #ddd;" class="form-control hoursminte" type="time" name="hour_time" id="hour_time" value="<?php echo esc_attr($entry['hour_time'] ?? ''); ?>" required>
                            <span id="hour_time_error" class="error"></span>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
        ?>
            <div class="medicine-group" id="medicine-group-1">
                <div class="row">
                    <div class="col-md-8">
                        <label for="medicine_name_1">Medicine Name</label>
                        <input class="form-control" type="text" name="medicine_name[]" id="medicine_name_1" required><br>
                        <span id="medicine_name_1_error" class="error"></span>
                    </div>
                    <div class="col-md-4">
                        <label for="medicine_name_1"></label>
                        <button type="button" id="add-more-medicine">Add more <i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label for="dose_type_1">Set Dose</label>
                        <select class="form-control" name="dose_type[]" id="dose_type_1" required>
                            <option value="spoon">Spoon</option>
                            <option value="ml">Milliliter</option>
                            <option value="mm">Millimeter</option>
                            <option value="number">Number</option>
                        </select>
                        <span id="dose_type_1_error" class="error"></span>
                    </div>
                    <div class="col-md-3">
                        <label for="">Value</label>
                        <style>
                        button.increment-button {
                        background: #000 !important;
                        margin: 0px !important;
                        border-radius: 50% !important;
                        padding: 3px !important;
                        height: 30px;
                        width: 30px;
                        position: absolute;
                        right: 15px;
                        
                        }
                        
                    button.decrement-button {
                        background: none !important;
                        color: #000 !important;
                        padding: 0px !important;
                        margin: 0px !important;
                        position: absolute;
                        left: 15px;
                    }
                    button.increment-button1 {
                        background: #000 !important;
                        margin: 0px !important;
                        border-radius: 50% !important;
                        padding: 3px !important;
                        height: 30px;
                        width: 30px;
                        position: absolute;
                        right: 15px;
                        
                        }
                        
                    button.decrement-button1 {
                        background: none !important;
                        color: #000 !important;
                        padding: 0px !important;
                        margin: 0px !important;
                        position: absolute;
                        left: 15px;
                    }
                    input#dose_value_1 {
                        border: none !important;
                        margin-bottom: 0px !important;
                        text-align: center;
                    }
                    input#duration_value_1 {
                        border: none !important;
                        margin-bottom: 0px !important;
                        text-align: center;
                    }

                    input#mobile_num {
                        width: 85% !important;
                        /* margin: 4px; */
                        /* height: 101% !important; */
                    }
                 </style>
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var inputField = document.getElementById("dose_value_1");
                    var incrementButton = document.querySelector(".increment-button");
                    var decrementButton = document.querySelector(".decrement-button");

                    console.log("Input Field:", inputField);
                    console.log("Increment Button:", incrementButton);
                    console.log("Decrement Button:", decrementButton);

                    incrementButton.addEventListener("click", function() {
                    inputField.stepUp();
                    });

                    decrementButton.addEventListener("click", function() {
                    inputField.stepDown();
                    });
                });
                </script>
                    <div class="d-flex flex-wrap justify-content-center align-items-center" style="gap: 10px;border:1px solid #ddd; border-radius:10px; position:relative;">
                    <button type="button" class="decrement-button">-</button> 
                    <input class="form-control w-25" type="number" name="dose_value[]" id="dose_value_1" value="0" min="0" required>  
                        <button type="button" class="increment-button">+</button></div> 
                        <span id="dose_value_1_error" class="error"></span>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="frequency_1">Add Frequency</label>
                        <select class="form-control" name="frequency[]" id="frequency_1" required>
                            <option value="daily">Just once</option>
                            <option value="onceaday">Once a day</option>
                            <option value="atnight">At night</option>
                            <option value="every4hrs">Every 4 hrs</option>
                            <option value="every6hrs">Every 6 hrs</option>
                            <option value="every8hrs">Every 8 hrs</option>
                            <option value="every12hrs">Every 12 hrs</option>
                        </select>
                        <span id="frequency_1_error" class="error"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label for="duration_type_1">Set Duration</label>
                        <select class="form-control" name="duration_type[]" id="duration_type_1" required>
                            <option value="Day">Day</option>    
                            <option value="Month">Month</option>
                            <option value="Year">Year</option>
                            <option value="Life-time">Life time</option>
                        </select>
                        <span id="duration_type_1_error" class="error"></span>
                    </div>

                    <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        var inputField = document.getElementById("duration_value_1");
                        var incrementButton = document.querySelector(".increment-button1");
                        var decrementButton = document.querySelector(".decrement-button1");

                        console.log("Input Field:", inputField);
                        console.log("Increment Button:", incrementButton);
                        console.log("Decrement Button:", decrementButton);

                        incrementButton.addEventListener("click", function() {
                        inputField.stepUp();
                        });

                        decrementButton.addEventListener("click", function() {
                        inputField.stepDown();
                        });
                    });
                    </script>
					
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            var addButton = document.getElementById('add-more-medicine');
                            addButton.addEventListener('click', function() {
                                // Check if all current medicine fields are filled
                                var medicineNameInputs = document.querySelectorAll('input[name^="medicine_name"]');
                                var allFilled = true;

                                medicineNameInputs.forEach(function(input) {
                                    if (input.value.trim() === '') {
                                        allFilled = false;
                                        // Show error message
                                        var errorSpan = document.getElementById(input.id + '_error');
                                        if (errorSpan) {
                                            errorSpan.textContent = 'Please fill all the fields before adding more.';
                                        }
                                    }
                                });

                                // If all current fields are filled, allow adding more
                                if (allFilled) {
                                    // Logic to add more fields here (you may need to adjust this part based on how you dynamically add fields)
                                }
                            });
                        });
                    </script>
                    <div class="col-md-3">
                        <label for="">Value</label>
                        <div class="d-flex flex-wrap justify-content-center align-items-center" style="gap: 10px;border:1px solid #ddd; border-radius:10px; position:relative;">
                        <button type="button" class="decrement-button1">-</button>
                        <input class="form-control w-50" type="number" value="0" name="duration_value[]" id="duration_value_1" min="0" required><br>
                        <button type="button" class="increment-button1">+</button>    
                    </div>
                        <span id="duration_value_1_error" class="error"></span>
                    </div>
                    <div class="col-md-3">
                        <label for="instruction_1">Medicine Instruction</label>
                        <select class="form-control" name="instruction[]" id="instruction_1" required>
                            <option value="beforefood">Before food</option>
                            <option value="withfood">With food</option>
                            <option value="afterfood">After food</option>
                        </select><br>
                        <span id="instruction_1_error" class="error"></span>
                    </div>
                    <div class="col-md-3">
                        <label for="time_type_1">Hours/Min</label>
                        <input style="border:1px solid #ddd;" class="form-control hoursminte" type="time" name="hour_time[]" id="hour_time" value="<?php echo esc_attr($entry['hour_time[]'] ?? ''); ?>" required>
                        <span id="hour_time_error" class="error"></span>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
    <div class="Frame-1000002092">
        <span class="Fill-below-info-to-add-reminder"> Other Information</span>
    </div>
    <div class="row">
    <div class="col-md-3">
        <label for="start_date">Start Date</label>
        <input style="border:1px solid #ddd;" class="form-control" type="date" name="start_date" id="start_date" min="<?php echo $current_date; ?>" value="<?php echo esc_attr($entry['start_date'] ?? ''); ?>" required><br>
        <span id="start_date_error" class="error"></span>
    </div>
    <div class="col-md-3">
        <label for="end_date">End Date</label>
        <input style="border:1px solid #ddd;" class="form-control" type="date" name="end_date" id="end_date" min="<?php echo $current_date; ?>" value="<?php echo esc_attr($entry['end_date'] ?? ''); ?>" required><br>
        <span id="end_date_error" class="error"></span>
    </div>
	<script>
    document.addEventListener("DOMContentLoaded", function() {
        var startDateField = document.getElementById("start_date");
        var endDateField = document.getElementById("end_date");
        var form = document.getElementById("pill-notifications-form");

        form.addEventListener("submit", function(event) {
            var startDate = new Date(startDateField.value);
            var endDate = new Date(endDateField.value);

            if (endDate < startDate) {
                event.preventDefault();
                alert("End date should be greater than or equal to the start date.");
                document.getElementById("end_date_error").textContent = "End date should be greater than or equal to the start date.";
            } else {
                document.getElementById("end_date_error").textContent = "";
            }
        });
    });
</script>
	
        <div class="col-md-6">
            
            <label for="mobile_num">Mobile Number</label>
			<div class="row align-items-center">
				<div class="col-2">
					<span value="234" style="border:1px solid #ddd;padding:15px;padding 0px; margin-bottom:14px; border-radius: 10px;background: #fff ">+234</span>
				</div>
				<div class="col">
					<span> <input style="border:1px solid #ddd;" class="form-control mobilephone ms-3 ms-sm-0"  oninput="validateNumberInput1(this)" type="number"  name="mobile_num" id="mobile_num" value="<?php echo esc_html($result['mobile_num']); ?>" placeholder="Enter your mobile number" required></span>
			</div>
            <div class="input-group "style="gap:5px;">
        <!-- Add more options for other country codes as needed -->
              </div>
               </div>
         
            <span id="mobile_num_error" class="error"></span>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-6">
            <span class="Add-Reminder-Time"> Add Reminder Time </span>
            <br>
            <br>
            <input style="border:1px solid #ddd;" class="form-control" type="time" name="reminder_time" id="reminder_time" value="<?php echo esc_attr($entry['reminder_time'] ?? ''); ?>" required><br>
            <span id="reminder_time_error" class="error"></span>
        </div>
    </div>
    <input type="submit" value="Add reminder">
</form>
    <?php
    return ob_get_clean();
}
//added functionality of edit page 

public function edit_pill_reminder_form() {
    if (isset($_GET['entry_id']) && is_user_logged_in()) {
        ob_start();
        global $wpdb;
        $entry_id = intval($_GET['entry_id']);
        $table_name = $wpdb->prefix . 'pill_notifications';
        $entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $entry_id), ARRAY_A);

        if (!$entry) {
            return '<p>No entry found for editing.</p>';
        }
        $current_date = date('d-m-Y');
        // Render your edit form HTML here
        ?>
       <style>
            #edit-pill-notifications-form {
                margin: auto;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 10px;
                background-color: #f9f9f9;
            }
            #edit-pill-notifications-form label {
                display: block;
                margin-bottom: 8px;
                font-weight: bold;
            }
            #edit-pill-notifications-form input[type="text"],
            #edit-pill-notifications-form input[type="number"],
            #edit-pill-notifications-form select {
                width: 100%;
                padding: 8px;
              
                border: 1px solid #ddd;
                border-radius: 5px;
                box-sizing: border-box;
            }
            #edit-pill-notifications-form button,
            #edit-pill-notifications-form input[type="submit"] {
                display: inline-block;
                padding: 10px 20px;
                margin-top: 10px;
                border: none;
                border-radius: 5px;
                background-color: #ff0000;
                color: #fff;
                cursor: pointer;
            }
            #edit-pill-notifications-form button:hover,
            #edit-pill-notifications-form input[type="submit"]:hover {
                background-color: #ff0000;
            }
            .medicine-group {
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 10px;
                margin-bottom: 20px;
                background-color: #fff;
            }
             .remove-medicine {
            background-color: #ff0000;
            color: #fff;
            padding: 12px;
            border: 0;
            border-radius: 5px;
             }
            .remove-medicine:hover {
                background-color: #ff0000;
            }

            .form-results-table {
                width: 100%;
                border-collapse: collapse;
            }

            .form-results-grid {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }

            .result-entry {
                display: grid;
                grid-template-columns: 150px 1fr;
                gap: 10px;
                padding: 15px;
                border: 1px solid #ccc;
                border-radius: 5px;
                background-color: #f9f9f9;
            }

            .entry-label {
                font-weight: bold;
            }

            .entry-value {
                font-style: italic;
            }
            .Start-Date {
                width: 85px;
                height: 24px;
                flex-grow: 0;
                font-family: Montserrat;
                font-size: 16px;
                font-weight: 600;
                font-stretch: normal;
                font-style: normal;
                line-height: 1.5;
                letter-spacing: normal;
                text-align: left;
                color: #1a1a1a;
                }
                .error {
                color: red;
                font-size: 12px;
                display: block;
                margin-top: -10px;
                margin-bottom: 10px;
            }
            .Add-Medicine-information {
                width: 275px;
                height: 24px;
                flex-grow: 0;
                font-family: Montserrat;
                font-size: 17px;
                font-weight: bold;
                font-stretch: normal;
                font-style: normal;
                line-height: normal;
                letter-spacing: normal;
                text-align: left;
                color: #1a1a1a;
                }
                .Fill-below-info-to-add-reminder {
                width: 323px;
                height: 15px;
                flex-grow: 0;
                font-family: Montserrat;
                font-size: 17px;
                font-weight: bold;
                font-stretch: normal;
                font-style: normal;
                line-height: normal;
                letter-spacing: normal;
                text-align: left;
                color: #1a1a1a;
                }
                .add-more-medicine {
                width: 163px;
                height: 24px;
                flex-grow: 0;
                font-family: Montserrat;
                font-size: 16px;
                font-weight: 600;
                font-stretch: normal;
                font-style: normal;
                line-height: 1.5;
                letter-spacing: normal;
                text-align: left;
                color: #ff0000;
                }
                .Add-Reminder-Time {
                width: 164px;
                height: 24px;
                flex-grow: 0;
                font-family: Montserrat;
                font-size: 16px;
                font-weight: 500;
                font-stretch: normal;
                font-style: normal;
                line-height: 1.5;
                letter-spacing: normal;
                text-align: left;
                color: #000;
                }
                .Frame-1000002091 {
                height: 58px;
                flex-grow: 1;
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding: 17px 20px;
                border-radius: 8px;
                border: solid 0.5px #d9d9d9;
                background-color: #f9f9f9;
                }
                .Frame-1000002092 {
                height: 60px;
                flex-grow: 1;
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                align-items: stretch;
                gap: 10px;
                padding: 0;
                }
        .-\37482927871 {
            
                height: 24px;
                flex-grow: 0;
                font-family: Montserrat;
                font-size: 16px;
                font-weight: 600;
                font-stretch: normal;
                font-style: normal;
                line-height: 1.5;
                letter-spacing: normal;
                text-align: left;
                color: #1a1a1a;
                }

                            .dropbtn {
                    background: none !important;
                    color: black;
                    padding: 10px;
                    font-size: 16px;
                    border: none;
                    cursor: pointer;
                }
				   button#add-more-medicine {
			margin-top: 26px;
		}
		   button.remove-medicine {
              margin-top: 25px !important;
              }

                .dropbtn:hover, .dropbtn:focus {
                    background-color: #ddd;
                }

                .dropdown-content {
                    display: none;
                    position: absolute;
                    right: 0;
                    background-color: #f9f9f9;
                    min-width: 160px;
                    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
                    z-index: 1;
                }

                .dropdown-content a {
                    color: black;
                    padding: 12px 16px;
                    text-decoration: none;
                    display: block;
                }

                .dropdown-content a:hover {
                    background-color: #f1f1f1;
                }             
                </style>
                <style>
                .switch {
                position: relative;
                display: inline-block;
                width: 60px;
                height: 34px;
                }

                .switch input { 
                opacity: 0;
                width: 0;
                height: 0;
                }

                .slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                -webkit-transition: .4s;
                transition: .4s;
                }

                .slider:before {
                position: absolute;
                content: "";
                height: 26px;
                width: 26px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                -webkit-transition: .4s;
                transition: .4s;
                }

                input:checked + .slider {
                background-color: #2196F3;
                }

                input:focus + .slider {
                box-shadow: 0 0 1px #2196F3;
                }

                input:checked + .slider:before {
                -webkit-transform: translateX(26px);
                -ms-transform: translateX(26px);
                transform: translateX(26px);
                }

                /* Rounded sliders */
                .slider.round {
                border-radius: 34px;
                }

                .slider.round:before {
                border-radius: 50%;
                }
            </style>
        <form action="<?php echo esc_url(admin_url('admin-post.php?action=save_pill_notification')); ?>" method="POST" id="edit-pill-notifications-form">
        <div class="Frame-1000002092">
        <span class="Fill-below-info-to-add-reminder"> Fill below info to add reminder</span>
    </div>
    <?php wp_nonce_field('save_pill_notification_nonce', 'pill_notification_nonce'); ?>
    <?php if ($entry) : ?>
        <input type="hidden" name="entry_id" value="<?php echo esc_attr($entry['id']); ?>">
    <?php endif; ?>
	 <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>"> <!-- Hidden field for user ID -->
    <label for="title_reminder">Title For Reminder</label>
    <input type="text" name="title_reminder" class="form-control" id="title_reminder" value="<?php echo esc_attr($entry['title_reminder'] ?? ''); ?>" required><br>
    <span id="title_reminder_error" class="error"></span>

    <div id="medicine-fields">
        <div class="Frame-1000002092">
            <span class="Fill-below-info-to-add-reminder"> Add Medicine information</span>
        </div>
        <?php
        $medicine_data = json_decode($entry['medicine_data'] ?? '[]', true);
        if (is_array($medicine_data) && !empty($medicine_data)) {
            foreach ($medicine_data as $index => $medicine) {
        ?>
    <div class="medicine-group" id="medicine-group-<?php echo $index + 1; ?>">
        <div class="row">
            <div class="col-md-6">
                <label for="medicine_name_<?php echo $index + 1; ?>">Medicine Name</label>
                <input type="text" name="medicine_name[]" class="form-control" id="medicine_name_<?php echo $index + 1; ?>" value="<?php echo esc_attr($medicine['medicine_name']); ?>" required><br>
                <span id="medicine_name_<?php echo $index + 1; ?>_error" class="error"></span>
            </div>
            <div class="col-md-6">
                <label for="add_more_<?php echo $index + 1; ?>"></label>
                <button  style="display: inline-block;
                    padding: 10px 20px;
                    margin-top: 10px;
                    border: none;
                    border-radius: 5px;
                    background-color: #ff0000;
                    color: #fff;
                    cursor: pointer; }" type="button" id="add-more-medicine">Add more <i class="fa-solid fa-plus"></i></button>
            </div>
        </div>
                    <div class="row">
                        <div class="col-md-3 mt-2">
                            <label for="dose_type_<?php echo $index + 1; ?>">Set Dose</label>
                            <select class="form-control" name="dose_type[]" id="dose_type_<?php echo $index + 1; ?>" required>
                                <option value="spoon" <?php selected($medicine['dose_type'], 'spoon'); ?>>Spoon</option>
                                <option value="ml" <?php selected($medicine['dose_type'], 'ml'); ?>>Milliliter</option>
                                <option value="mm" <?php selected($medicine['dose_type'], 'mm'); ?>>Millimeter</option>
                                <option value="number" <?php selected($medicine['dose_type'], 'number'); ?>>Number</option>
                            </select><br>
                        </div>
                        <div class="col-md-3 mt-2">
                            <label class="mt-2" for="">Value</label>
                            <span id="dose_type_<?php echo $index + 1; ?>_error" class="error"></span>
                            <input class="form-control" type="number" name="dose_value[]" id="dose_value_<?php echo $index + 1; ?>" value="<?php echo esc_attr($medicine['dose_value']); ?>" min="0" required><br>
                            <span id="dose_value_<?php echo $index + 1; ?>_error" class="error"></span>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label for="frequency_<?php echo $index + 1; ?>">Add Frequency</label>
                            <select name="frequency[]" id="frequency_<?php echo $index + 1; ?>" required>
                                <option value="select" <?php selected($medicine['frequency'], 'select'); ?>>Select</option>
                                <option value="daily" <?php selected($medicine['frequency'], 'daily'); ?>>Just once</option>
                                <option value="onceaday" <?php selected($medicine['frequency'], 'onceaday'); ?>>Once a day</option>
                                <option value="atnight" <?php selected($medicine['frequency'], 'atnight'); ?>>At night</option>
                                <option value="every4hrs" <?php selected($medicine['frequency'], 'every4hrs'); ?>>Every 4 hrs</option>
                                <option value="every6hrs" <?php selected($medicine['frequency'], 'every6hrs'); ?>>Every 6 hrs</option>
                                <option value="every8hrs" <?php selected($medicine['frequency'], 'every8hrs'); ?>>Every 8 hrs</option>
                                <option value="every12hrs" <?php selected($medicine['frequency'], 'every12hrs'); ?>>Every 12hrs</option>
                            </select><br>
                            <span id="frequency_<?php echo $index + 1; ?>_error" class="error"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="duration_type_<?php echo $index + 1; ?>">Set Duration</label>
                            <select name="duration_type[]" id="duration_type_<?php echo $index + 1; ?>" required>
                                <option value="Day" <?php selected($medicine['duration_type'], 'Day'); ?>>Day</option>
                                <option value="Month" <?php selected($medicine['duration_type'], 'Month'); ?>>Month</option>
                                <option value="Year" <?php selected($medicine['duration_type'], 'Year'); ?>>Year</option>
                                <option value="Life-time" <?php selected($medicine['duration_type'], 'Life-time'); ?>>Life time</option>
                            </select><br>
                        </div>
                        <div class="col-md-3">
                            <label class="mt-2" for="">Value</label>
                            <span id="duration_type_<?php echo $index + 1; ?>_error" class="error"></span>
                            <input class="form-control" type="number" name="duration_value[]" id="duration_value_<?php echo $index + 1; ?>" value="<?php echo esc_attr($medicine['duration_value']); ?>" min="0" required><br>
                            <span id="duration_value_<?php echo $index + 1; ?>_error" class="error"></span>
                        </div>
                        <div class="col-md-3">
                            <label for="instruction_<?php echo $index + 1; ?>">Medicine Instruction</label>
                            <select name="instruction[]" id="instruction_<?php echo $index + 1; ?>" required>
                                <option value="beforefood" <?php selected($medicine['instruction'], 'beforefood'); ?>>Before food</option>
                                <option value="withfood" <?php selected($medicine['instruction'], 'withfood'); ?>>With food</option>
                                <option value="afterfood" <?php selected($medicine['instruction'], 'afterfood'); ?>>After food</option>
                            </select><br>
                            <span id="instruction_<?php echo $index + 1; ?>_error" class="error"></span>
                        </div>
                        <div class="col-md-3">
                            <label for="time_type_1">Hours/Min</label>
                            <input style="border:1px solid #ddd;" class="form-control hoursminte" type="time" name="hour_time" id="hour_time" value="<?php echo esc_attr($entry['hour_time'] ?? ''); ?>" required>
                            <span id="hour_time_error" class="error"></span>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
        ?>
            <div class="medicine-group" id="medicine-group-1">
                <div class="row">
                    <div class="col-md-8">
                        <label for="medicine_name_1">Medicine Name</label>
                        <input class="form-control" type="text" name="medicine_name[]" id="medicine_name_1" required><br>
                        <span id="medicine_name_1_error" class="error"></span>
                    </div>
                    <div class="col-md-4">
                        <label for="medicine_name_1"></label>
                        <button  style="display: inline-block; padding: 10px 20px; margin-top: 10px; border: none; border-radius: 5px; background-color: #ff0000; color: #fff; cursor: pointer; }"  type="button" id="add-more-medicine">Add more <i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label for="dose_type_1">Set Dose</label>
                        <select class="form-control" name="dose_type[]" id="dose_type_1" required>
                            <option value="spoon">Spoon</option>
                            <option value="ml">Milliliter</option>
                            <option value="mm">Millimeter</option>
                            <option value="number">Number</option>
                        </select>
                        <span id="dose_type_1_error" class="error"></span>
                    </div>
                    <div class="col-md-3">
                        <label for="">Value</label>
                <style>
                        button.increment-button {
                        background: #000 !important;
                        margin: 0px !important;
                        border-radius: 50% !important;
                        padding: 3px !important;
                        height: 30px;
                        width: 30px;
                        position: absolute;
                        right: 15px;
                        
                        }
                        
                    button.decrement-button {
                        background: none !important;
                        color: #000 !important;
                        padding: 0px !important;
                        margin: 0px !important;
                        position: absolute;
                        left: 15px;
                    }
                    button.increment-button1 {
                        background: #000 !important;
                        margin: 0px !important;
                        border-radius: 50% !important;
                        padding: 3px !important;
                        height: 30px;
                        width: 30px;
                        position: absolute;
                        right: 15px;
                        
                        }
                        
                    button.decrement-button1 {
                        background: none !important;
                        color: #000 !important;
                        padding: 0px !important;
                        margin: 0px !important;
                        position: absolute;
                        left: 15px;
                    }
                    input#dose_value_1 {
                        border: none !important;
                        margin-bottom: 0px !important;
                        text-align: center;
                    }
                    input#duration_value_1 {
                        border: none !important;
                        margin-bottom: 0px !important;
                        text-align: center;
                    }

                    input#mobile_num {
                        width: 85% !important;
                        /* margin: 4px; */
                        /* height: 101% !important; */
                    }

                   </style>
                 <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        var inputField = document.getElementById("dose_value_1");
                        var incrementButton = document.querySelector(".increment-button");
                        var decrementButton = document.querySelector(".decrement-button");

                        console.log("Input Field:", inputField);
                        console.log("Increment Button:", incrementButton);
                        console.log("Decrement Button:", decrementButton);

                        incrementButton.addEventListener("click", function() {
                        inputField.stepUp();
                        });

                        decrementButton.addEventListener("click", function() {
                        inputField.stepDown();
                        });
                    });
                    </script>
                    <div class="d-flex flex-wrap justify-content-center align-items-center" style="gap: 10px;border:1px solid #ddd; border-radius:10px; position:relative;">
                    <button type="button" class="decrement-button">-</button> 
                    <input class="form-control w-25 " type="number" name="dose_value[]" id="dose_value_1" value="0" min="0" required>                                       
                        
                        <button type="button" class="increment-button">+</button></div> 
                        <span id="dose_value_1_error" class="error"></span>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="frequency_1">Add Frequency</label>
                        <select class="form-control" name="frequency[]" id="frequency_1" required>
                            <option value="daily">Just once</option>
                            <option value="onceaday">Once a day</option>
                            <option value="atnight">At night</option>
                            <option value="every4hrs">Every 4 hrs</option>
                            <option value="every6hrs">Every 6 hrs</option>
                            <option value="every8hrs">Every 8 hrs</option>
                            <option value="every12hrs">Every 12 hrs</option>
                        </select>
                        <span id="frequency_1_error" class="error"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label for="duration_type_1">Set Duration</label>
                        <select class="form-control" name="duration_type[]" id="duration_type_1" required>
                            <option value="Day">Day</option>    
                            <option value="Month">Month</option>
                            <option value="Year">Year</option>
                            <option value="Life-time">Life time</option>
                        </select>
                        <span id="duration_type_1_error" class="error"></span>
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            var inputField = document.getElementById("duration_value_1");
                            var incrementButton = document.querySelector(".increment-button1");
                            var decrementButton = document.querySelector(".decrement-button1");

                            console.log("Input Field:", inputField);
                            console.log("Increment Button:", incrementButton);
                            console.log("Decrement Button:", decrementButton);

                            incrementButton.addEventListener("click", function() {
                            inputField.stepUp();
                            });

                            decrementButton.addEventListener("click", function() {
                            inputField.stepDown();
                            });
                        });
                    </script>
					
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        var addButton = document.getElementById('add-more-medicine');
                        addButton.addEventListener('click', function() {
                            // Check if all current medicine fields are filled
                            var medicineNameInputs = document.querySelectorAll('input[name^="medicine_name"]');
                            var allFilled = true;

                            medicineNameInputs.forEach(function(input) {
                                if (input.value.trim() === '') {
                                    allFilled = false;
                                    // Show error message
                                    var errorSpan = document.getElementById(input.id + '_error');
                                    if (errorSpan) {
                                        errorSpan.textContent = 'Please fill all the fields before adding more.';
                                    }
                                }
                            });

                            // If all current fields are filled, allow adding more
                            if (allFilled) {
                                // Logic to add more fields here (you may need to adjust this part based on how you dynamically add fields)
                            }
                        });
                    });
                </script>
                    <div class="col-md-3">
                        <label for="">Value</label>
                        <div class="d-flex flex-wrap justify-content-center align-items-center" style="gap: 10px;border:1px solid #ddd; border-radius:10px; position:relative;">
                        <button type="button" class="decrement-button1">-</button>
                        <input class="form-control w-50" type="number" value="0" name="duration_value[]" id="duration_value_1" min="0" required><br>
                        <button type="button" class="increment-button1">+</button>    
                    </div>
                        <span id="duration_value_1_error" class="error"></span>
                    </div>
                    <div class="col-md-3">
                        <label for="instruction_1">Medicine Instruction</label>
                        <select class="form-control" name="instruction[]" id="instruction_1" required>
                            <option value="beforefood">Before food</option>
                            <option value="withfood">With food</option>
                            <option value="afterfood">After food</option>
                        </select><br>
                        <span id="instruction_1_error" class="error"></span>
                    </div>
                    <div class="col-md-3">
                        <label for="time_type_1">Hours/Min</label>
                        <input style="border:1px solid #ddd;" class="form-control hoursminte" type="time" name="hour_time" id="hour_time" value="<?php echo esc_attr($entry['hour_time'] ?? ''); ?>" required>
                        <span id="hour_time_error" class="error"></span>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>

    <div class="Frame-1000002092">
        <span class="Fill-below-info-to-add-reminder"> Other Information</span>
    </div>
    <div class="row">
    <div class="col-md-3">
        <label for="start_date">Start Date</label>
        <input style="border:1px solid #ddd;" class="form-control" type="date" name="start_date" id="start_date" min="<?php echo $current_date; ?>" value="<?php echo esc_attr($entry['start_date'] ?? ''); ?>" required><br>
        <span id="start_date_error" class="error"></span>
    </div>
    <div class="col-md-3">
        <label for="end_date">End Date</label>
        <input style="border:1px solid #ddd;" class="form-control" type="date" name="end_date" id="end_date" min="<?php echo $current_date; ?>" value="<?php echo esc_attr($entry['end_date'] ?? ''); ?>" required><br>
        <span id="end_date_error" class="error"></span>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var startDateField = document.getElementById("start_date");
        var endDateField = document.getElementById("end_date");
        var form = document.getElementById("edit-pill-notifications-form");

        form.addEventListener("submit", function(event) {
            var startDate = new Date(startDateField.value);
            var endDate = new Date(endDateField.value);

            if (endDate < startDate) {
                event.preventDefault();
                alert("End date should be greater than or equal to the start date.");
                document.getElementById("end_date_error").textContent = "End date should be greater than or equal to the start date.";
            } else {
                document.getElementById("end_date_error").textContent = "";
            }
        });
    });
</script>
	
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'pill_notifications';

        // Assuming you have an ID or some identifier to fetch the specific row
        $id = 1; // Replace with the actual ID or dynamic value
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);

        // Ensure $result is an array and the 'mobile_num' key exists
        $mobile_num = isset($result['mobile_num']) ? esc_html($result['mobile_num']) : '';

        ?>
        <div class="col-md-6">
            <label for="mobile_num">Mobile Number</label>
            <div class="row align-items-center">
                <div class="col-2">
                    <span value="234" style="border:1px solid #ddd;padding:15px; margin-bottom:14px; border-radius: 10px;background: #fff ">+234</span>
                </div>
                <div class="col-10">
                    <span>
                        <input style="border:1px solid #ddd;"  class="form-control mobilephone" type="number" pattern=".{10,10}" name="mobile_num" id="mobile_num" value="<?php echo esc_attr($entry['mobile_num'] ?? ''); ?>" placeholder="Enter your mobile number"  oninput="validateNumberInput1(this)" required >
                    </span>
                </div>
                <div class="input-group" style="gap:5px;">
                    <!-- Add more options for other country codes as needed -->
                </div>
            </div>
            <span id="mobile_num_error" class="error"></span>
        </div>
    <br>
    <div class="row">
        <div class="col-md-6">
            <span class="Add-Reminder-Time"> Add Reminder Time </span>
            <br>
            <br>
            <input style="border:1px solid #ddd;" class="form-control" type="time" name="reminder_time" id="reminder_time" value="<?php echo esc_attr($entry['reminder_time'] ?? ''); ?>" required><br>
            <span id="reminder_time_error" class="error"></span>
        </div>
    </div>
    <div>
    <input style="display: inline-block;
            padding: 10px 20px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            background-color: #ff0000;
            color: #fff;
            cursor: pointer;
    "  type="submit" 
        value="Update reminder">
</div>
        </form>
        <?php

        return ob_get_clean();
    } else {
        return '<p>You do not have sufficient permissions to access this page.</p>';
    }
}

public function save_form_data() {
    // Check nonce
                if (!isset($_POST['pill_notification_nonce']) || !wp_verify_nonce($_POST['pill_notification_nonce'], 'save_pill_notification_nonce')) {
                    wp_die('Security check failed');
                }

                // Check if required fields are set
                if (isset($_POST['title_reminder']) && isset($_POST['medicine_name'])) {
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'pill_notifications';

                    // Sanitize and prepare medicine data
                    $medicine_data = [];
                    $medicine_count = count($_POST['medicine_name']);
                    for ($i = 0; $i < $medicine_count; $i++) {
                        $medicine_data[] = [
                            'medicine_name' => sanitize_text_field($_POST['medicine_name'][$i]),
                            'dose_type' => sanitize_text_field($_POST['dose_type'][$i]),
                            'dose_value' => intval($_POST['dose_value'][$i]),
                            'duration_type' => sanitize_text_field($_POST['duration_type'][$i]),
                            'duration_value' => intval($_POST['duration_value'][$i]),
                            'hour_time' => sanitize_text_field($_POST['hour_time'][$i]),
                            'frequency' => sanitize_text_field($_POST['frequency'][$i]),
                            'instruction' => sanitize_text_field($_POST['instruction'][$i]),
                            'reminder_time' => sanitize_text_field($_POST['reminder_time'][$i]),
                        ];
                    }

                    // Prepare data to be inserted or updated
                    $data = array(
                        'user_id' => get_current_user_id(), // Add user ID to data
                        'title_reminder' => sanitize_text_field($_POST['title_reminder']),
                        'start_date' => sanitize_text_field($_POST['start_date']),
                        'end_date' => sanitize_text_field($_POST['end_date']),
                        'reminder_time' => sanitize_text_field($_POST['reminder_time']),
                        'hour_time' => sanitize_text_field($_POST['hour_time']),
                        'mobile_num' => sanitize_text_field(ltrim($_POST['mobile_num'], '0')),
                        'medicine_data' => wp_json_encode($medicine_data),
                    );

                    // Check if updating existing entry or inserting new one
                    if (isset($_POST['entry_id'])) {
                        $entry_id = intval($_POST['entry_id']);
                        $wpdb->update($table_name, $data, array('id' => $entry_id), array('%d', '%s', '%s', '%s', '%s', '%s', '%s','%s'), array('%d'));
                    } else {
                        $wpdb->insert($table_name, $data);
                    }
                }

                // Redirect user after saving data
                wp_redirect(home_url('/pill-reminders/'));
                exit;
            }

public function display_form_results() {
			    // Check if user is logged in
    if (!is_user_logged_in()) {
        return '<p class="loginmessage">You need to be logged in to access this form here <a href="https://mophethonline.com/login/">Login</a>.</p>';
    }
    global $wpdb;

    // Check if entry_id parameter is set in the URL
    if (isset($_GET['entry_id'])) {
        $entry_id = intval($_GET['entry_id']); // Sanitize input to prevent SQL injection
        $table_name = $wpdb->prefix . 'pill_notifications';
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $entry_id), ARRAY_A);

        ob_start();
        ?>

    </div>
        <div>
            <?php if (!empty($results)) : ?>
                <?php $result = $results[0]; // Only one result expected since ID is unique ?>
                    <div class="row p-3">
                      <div class="col-md-6">
                            <h2><span><?php echo esc_html($result['title_reminder']); ?></span></h2>
                        </div>
                        <div class="col-md-6">
                         <a class="edit-detailsbutton" style="background:red !important; padding: 10px 15px; font-size: 15px; float: right; color: #fff; border-radius: 5px;" href="<?php echo esc_url(home_url('/edit-pill-form/?entry_id=' . $result['id'])); ?>">Edit details</a>
                        </div>
                    </div>
                <?php
                $medicine_data = json_decode($result['medicine_data'], true);
                if (is_array($medicine_data) && !empty($medicine_data)) :
                    foreach ($medicine_data as $medicine) :
                ?>
                        <div class="row edit-details container p-5" style="border:1px solid #e0e3e9;">

                            <div class="col-md-6" style="border-bottom: 1px solid #e0e3e9;">
                                <h6>Medicine Name: <span><?php echo esc_html($medicine['medicine_name']); ?></span></h6>
                                <h6>Frequency: <span><?php echo esc_html($medicine['frequency']); ?></span></h6>
                                <?php if (isset($medicine['instruction'])) : ?>
                                    <h6>Instruction: <span><?php echo esc_html($medicine['instruction']); ?></span></h6>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6" style="border-bottom: 1px solid #e0e3e9;">
                                <h6>Dose: <span><?php echo esc_html($medicine['dose_value'] . ' ' . $medicine['dose_type']); ?></span></h6>
                                <h6>Duration: <span><?php echo esc_html($medicine['duration_value'] . ' ' . $medicine['duration_type']); ?></span></h6>
                            </div>
                            <div>
                                <div class="row">
                                    <div class="col-md-6 mt-3">
                                        <h6>Reminder Date: <span>
                                            <?php
                                                $start_date = new DateTime($result['start_date']);
                                                $end_date = new DateTime($result['end_date']);
                                                echo esc_html($start_date->format('d/m/y')) . ' - ' . esc_html($end_date->format('d/m/y'));
                                            ?>
                                        </span></h6>
                                    </div>
                                    <div class="col-md-6 mt-3">
                                        <h6>Reminder Time: <span><?php echo esc_html($result['reminder_time']); ?></span></h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h6>Phone Number: <span><?php echo esc_html($result['mobile_num']); ?></span></h6>
                            </div>
                        </div>
                        <br>
                <?php
                    endforeach;
                endif;
                ?>
            <?php else : ?>
                <p>No form submissions found.</p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    } else {
        // Redirect or display an error if entry_id parameter is not set
        wp_redirect(home_url()); // Redirect to homepage if entry_id is missing
        exit;
    }
}
public function display_medicine_listing() {
			    // Check if user is logged in
    if (!is_user_logged_in()) {
        return '<p class="loginmessage"> Please <a href="' . home_url('/login') . '">Login</a> or <a href="' . home_url('/register-user') . '">Sign-up</a> for your account and then check Pill Reminders list.</p>';
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'pill_notifications';
    $user_id = get_current_user_id(); // Get current user ID
    $results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id),
        ARRAY_A
    );
    wp_enqueue_script('my-custom-script', get_stylesheet_directory_uri() . '/js/custom-script.js', array('jquery'), '1.0', true);
    wp_localize_script('my-custom-script', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
    ob_start();
    ?>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="Frame-1000002092">
                    <h2 class="Fill-below-info-to-add-reminder">Your pill reminders</h2>
                </div>
            </div>
            <div class="col-md-6">
                <a style="background: red !important;
            padding: 10px 15px;
             font-size: 15px;
             float :right;
             color: #fff;
            border-radius: 5px;" href="<?php echo home_url('/add-pill-reminder/'); ?>">
                   Add new reminder
                </a>
            </div>
        </div>
        <div class="row">
            <?php if (!empty($results)) : ?>
                <?php foreach ($results as $result) : ?>
                    <?php
                    $medicine_data = json_decode($result['medicine_data'], true);
                    $status = isset($result['status']) ? $result['status'] : 0;
                    $status_text = $status ? 'Active' : 'Inactive';
                    $status_color = $status ? 'green' : 'red';
                    if (is_array($medicine_data) && !empty($medicine_data)) :
                        foreach ($medicine_data as $medicine) :
                            ?>
                            <div class="col-md-6 p-2">
                                <div class="list-page" style="align-items: flex-start;
                                    gap: 30px;
                                    padding: 30px;
                                    border-radius: 8px;
                                    color:#000;
                                    border: solid 0.5px #d9d9d9;
                                    ">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <a href="<?php echo esc_url(home_url('/view-reminders/?entry_id=' . $result['id'])); ?>"> <h2><?php echo esc_html($result['title_reminder']); ?></h2></a>
                                       
                                        <!-- <button class="toggle-status" data-medicine-id="<?php echo $result['id']; ?>" data-current-status="<?php echo $status; ?>">Toggle Status</button> -->
                                        <div class="medicine-item" data-medicine-id="<?php echo $result['id']; ?>">
                        <label class="switch">
                        <input type="checkbox" class="toggle-status" data-current-status="<?php echo $status; ?>" <?php echo $status ? 'checked' : ''; ?>>
                        <span class="slider round"></span>
                        </label>
                        <span class="status-indicator" style="color: <?php echo $status_color; ?>;"><?php echo $status_text; ?></span>
                    </div>
                    <style>
									.switch {
									  position: relative;
									  display: inline-block;
									  width: 44px;
									  height: 24px;
									}

									.switch input { 
									  opacity: 0;
									  width: 0;
									  height: 0;
									}

									.slider {
									  position: absolute;
									  cursor: pointer;
									  top: 0;
									  left: 0;
									  right: 0;
									  bottom: 0;
									  background-color: red;
									  -webkit-transition: .4s;
									  transition: .4s;
									}

									.slider:before {
									  position: absolute;
									  content: "";
									  height: 15px;
									  width: 15px;
									  left: 4px;
									  bottom: 4px;
									  background-color: white;
									  -webkit-transition: .4s;
									  transition: .4s;
									}

									input:checked + .slider {
									  background-color: green;
									}

									input:focus + .slider {
									  box-shadow: 0 0 1px #2196F3;
									}

									input:checked + .slider:before {
									  -webkit-transform: translateX(20px);
									  -ms-transform: translateX(20px);
									  transform: translateX(20px);
									}

									/* Rounded sliders */
									.slider.round {
									  border-radius: 34px;
									}

									.slider.round:before {
									  border-radius: 50%;
									}
									</style>


                                    <style>
                                        .dropdown:hover .dropdown-content {
                                            display: block;
                                        }
                                        
                                        .dropdown-content {
                                            display: none;
                                            position: absolute;
                                            background-color: #f9f9f9;
                                            min-width: 160px;
                                            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
                                            z-index: 1;
                                        }

                                        .dropdown-content a {
                                            color: black;
                                            padding: 12px 16px;
                                            text-decoration: none;
                                            display: block;
                                        }

                                        .dropdown-content a:hover {
                                            background-color: #f1f1f1;
                                        }
                                    </style>

									<div class="dropdown" style="position: relative;">
										<div class="dropbtn" style="background: none; color:black; border: none; font-weight: 700; font-size: 28px;">⋮</div>
										<div class="dropdown-content">
											<a href="<?php echo esc_url(home_url('/edit-pill-form/?entry_id=' . $result['id'])); ?>">Edit</a>
											<a href="<?php echo esc_url(admin_url('admin-post.php?action=delete_pill_notification&entry_id=' . $result['id'])); ?>">Delete</a>
										</div>
									</div>
                                    </div>
                                    <h6>Reminder Date: <?php
                                        $start_date = new DateTime($result['start_date']);
                                        $end_date = new DateTime($result['end_date']);
                                        echo esc_html($start_date->format('d/m/y')) . ' - ' . esc_html($end_date->format('d/m/y'));
                                    ?></h6>
                                    <!-- <h6>Dose: <?php echo esc_html($medicine['dose_value'] . ' ' . $medicine['dose_type']); ?></h6>
                                    <h6>Medical Instruction: <?php echo esc_html($medicine['instruction']); ?></h6> -->
                                </div>
                            </div>
                        <?php
                        endforeach;
                    endif;
                    ?>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No medicine listings found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function toggleDropdown(button) {
        var dropdownContent = button.nextElementSibling;
        if (dropdownContent.style.display === "block") {
            dropdownContent.style.display = "none";
        } else {
            dropdownContent.style.display = "block";
        }
    }

    window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.style.display === "block") {
                    openDropdown.style.display = "none";
                }
            }
        }
    }
    </script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Assuming your checkbox has a class 'toggle-status'
        $('.toggle-status').change(function() {
            var isChecked = $(this).prop('checked'); // Check if checkbox is checked
            var statusTextElement = $(this).closest('.medicine-item').find('.status-indicator'); // Find the status text element
            
            // Example logic based on isChecked (you can customize this)
            if (isChecked) {
                
                statusTextElement.html('<span style="color:green">Active</span>'); // Change text if checked
            } else {
                statusTextElement.html('<span style="color:red">Inactive</span>'); // Change text if unchecked
            }

            // You can replace the above logic with your own based on your requirements
            // You might want to fetch data from server or update based on some other condition
        });
    });
</script>

<script>
jQuery(function($) {
    // Function to fetch initial statuses on page load
    function fetchInitialStatuses() {
        // Iterate over each checkbox
        $('.toggle-status').each(function() {
            var checkbox = $(this);
            var medicineId = checkbox.closest('.medicine-item').data('medicine-id');

            $.ajax({
                url: ajax_object.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_medicine_status',
                    medicine_id: medicineId
                },
                success: function(response) {
                    if (response.success) {
                        var currentStatus = response.data.status; // Assuming 'status' is returned from server

                        // Update checkbox state and status indicator
                        checkbox.prop('checked', currentStatus == 1);
                        var statusText = currentStatus == 1 ? 'Active' : 'Inactive';
                        var statusColor = currentStatus == 1 ? 'green' : 'red';
                        checkbox.siblings('.status-indicator').text(statusText).css('color', statusColor);
                    } else {
                        console.error('Failed to fetch initial status.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ' + status + ' - ' + error);
                }
            });
        });
    }

    // Call fetchInitialStatuses() on document ready
    $(document).ready(function() {
        fetchInitialStatuses();
    });

    // Toggle status change handler (similar to your existing code)
    $('.toggle-status').on('change', function() {
        var checkbox = $(this);
        var isChecked = checkbox.prop('checked') ? 1 : 0;
        var medicineId = checkbox.closest('.medicine-item').data('medicine-id');

        $.ajax({
            url: ajax_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'update_medicine_status',
                medicine_id: medicineId,
                new_status: isChecked
            },
            success: function(response) {
                if (response.success) {
                    var newStatusText = isChecked ? 'Active' : 'Inactive';
                    var newStatusColor = isChecked ? 'green' : 'red';
                    checkbox.data('current-status', isChecked);
                    checkbox.siblings('.status-indicator').text(newStatusText).css('color', newStatusColor);
                } else {
                    console.error('Failed to update status.');
                    checkbox.prop('checked', !checkbox.prop('checked'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ' + status + ' - ' + error);
                checkbox.prop('checked', !checkbox.prop('checked'));
            }
        });
    });
});
</script>

    <?php
    return ob_get_clean();
}

    
    public function enqueue_scripts() {
        
        wp_enqueue_script('pill-notifications-js', plugin_dir_url(__FILE__) . 'pill-notifications.js', array('jquery'), null, true);
    }
}

new PillNotifications();
?>

