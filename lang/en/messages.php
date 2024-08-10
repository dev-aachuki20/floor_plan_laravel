<?php

return [

    'crud' => [
        'add_record'    => 'Successfully Added !',
        'update_record' => 'Successfully Updated !',
        'delete_record' => 'This record has been succesfully deleted!',
        'restore_record' => 'This record has been succesfully Restored!',
        'merge_record'  => 'This record has been succesfully Merged!',
        'approve_record' => 'Record Successfully Approved !',
        'status_update' => 'Status successfully updated!',
    ],

    'unable_to_add_blank_field' => 'Sorry, Unable to add a blank field in',
    'data_already_exists' => 'Sorry, You cannot create new with the same name so use existing.',

    'areYouSure' => 'Are you sure you want to delete this record?',
    'areYouSureapprove' => 'Are you sure you want to Approve this record?',
    'areYouSurerestore' => 'Are you sure you want to Restore this Database? It will delete your current database.',
    'deletetitle' => 'Delete Confirmation',
    'restoretitle' => 'Restore Confirmation',
    'approvaltitle' => 'Approval Confirmation',
    'areYouSureRestore' => 'Are you sure you want to restore this record?',
    'error_message'   => 'Something went wrong....please try again later!',
    'no_record_found' => 'No Records Found!',
    'suspened' => "Your account has been suspened!",
    'invalid_email' => 'Invalid Email',
    'invalid_otp' => 'Invalid OTP',
    'invalid_pin' => 'Invalid PIN',
    'wrong_credentials' => 'These credentials do not match our records!',
    'not_activate' => 'Your account is not activated.',
    'otp_sent_email' => 'We have successfully sent OTP on your Registered Email',
    'expire_otp' => 'OTP has been Expired',
    'verified_otp' => 'OTP successfully Verified.',
    'invalid_token_email' => 'Invalid Token or Email!',
    'success' => 'Success',
    'register_success' => 'Registration successful! Please check your email for a verification link.',
    'login_success' => 'You have logged in successfully!',
    'logout_success' => 'Logged out successfully!',
    'warning_select_record' => 'Please select at least one record',
    'required_role' => "User with the specified email doesn't have the required role.",

    'invalid_token'                 => 'Your access token has been expired. Please login again.',
    'not_authorized'                => 'Not Authorized to access this resource/api',
    'not_found'                     => 'Not Found!',
    'endpoint_not_found'            => 'Endpoint not found',
    'resource_not_found'            => 'Resource not found',
    'token_invalid'                 => 'Token is invalid',
    'unexpected'                    => 'Unexpected Exception. Try later',

    'data_retrieved_successfully'   => 'Data retrieved successfully',
    'record_retrieved_successfully' => 'Record retrieved successfully',
    'record_created_successfully'   => 'Record created successfully',
    'record_updated_successfully'   => 'Record updated successfully',
    'record_deleted_successfully'   => 'Record deleted successfully',
    'record_saved_successfully'     => 'Record saved successfully',
    'password_updated_successfully' => 'Password updated successfully',

    'profile_updated_successfully'  => 'Profile updated successfully',
    'account_deactivate'            => 'Your account has been deactivated. Please contact the admin.',
    'staff_account_deactivate'      => 'Your account has been deactivated.',

    'user_not_found'                => 'User Not Fund',
    'user_created_successfully'     => 'User created successfully',
    'user_deleted_successfully'     => 'User deleted successfully',
    'user_updated_successfully'     => 'User updated successfully',
    'user_record_retrieved_successfully'     => 'User data retrieved successfully',
    'invalid_password'              => 'Password does not match',
    'user_created_and_welcome_email_sent'  => 'User created successfully and a welcome email has been sent.',
    'user_updated_and_email_sent'  => 'User updated successfully and email has been sent.',

    'reports' => [
        'speciality' => [
            'title'       => 'Surgeons',
            'description' => 'The following graphic shows the level of progress of prepared surgeons over the week in the selected hospital.'
        ],
        'anaesthetics' => [
            'title'       => 'Anaesthetics',
            'description' => 'The following graphic shows the level of progress of prepared anaesthetics over the week in the selected hospital.'
        ],
        'staff' => [
            'title'       => 'Staff',
            'description' => 'The following graphic shows the level of progress of prepared staff over the week in the selected hospital.'
        ],
        'overview' => [
            'title' => 'Overview',
            'descriptiion' => 'The following graphic shows the level of progress in approved surgical procedures throughout the week in the selected hospital.',
        ]
    ]
];
