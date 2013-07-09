<?php
$config = array(
    'update_profile'    =>  array(
        array(
			'field'	=>	'name',
			'label'	=>	'name',
			'rules'	=>	'required|twitter_user_name|xss_clean'
		),
        array(
			'field'	=>	'location',
			'label'	=>	'location',
			'rules'	=>	'required|alpha_unicode|xss_clean'
		),
        array(
			'field'	=>	'email',
			'label'	=>	'email',
			'rules'	=>	'required|valid_email|xss_clean'
		)
    ),
    'authentication'    =>  array(
        array(
			'field'	=>	'email',
			'label'	=>	'email',
			'rules'	=>	'required|valid_email|xss_clean'
		),
        array(
			'field'	=>	'password',
			'label'	=>	'password',
			'rules'	=>	'required|xss_clean'
		)
    ),
	'twitter_profile_confirmation'	=> array(
		array(
			'field'	=>	'name',
			'label'	=>	'name',
			'rules'	=>	'required|twitter_user_name|xss_clean'
		),
        array(
			'field'	=>	'location',
			'label'	=>	'location',
			'rules'	=>	'required|alpha_unicode|xss_clean'
		),
        array(
			'field'	=>	'email',
			'label'	=>	'email',
			'rules'	=>	'required|valid_email|xss_clean'
		),
        array(
			'field'	=>	'password',
			'label'	=>	'password',
			'rules'	=>	'required|xss_clean'
		),
        array(
			'field'	=>	'password_repeat',
			'label'	=>	'password_repeat',
			'rules'	=>	'required|matches[password]|xss_clean'
		)
    ),
    'twitter_pin_verification'  =>  array(
        array(
            'field' =>  'pin',
            'label' =>  'pin',
            'rules' =>  'required|numeric|xss_clean'
        )
    ),
    'contact_form'  =>  array(
        array(
			'field'	=>	'name',
			'label'	=>	'name',
			'rules'	=>	'required|alpha_unicode|xss_clean'
		),
        array(
			'field'	=>	'email',
			'label'	=>	'email',
			'rules'	=>	'required|valid_email|xss_clean'
		),
        array(
			'field'	=>	'question',
			'label'	=>	'question',
			'rules'	=>	'required|min_length[8]|xss_clean'
		),
    )
);
?>