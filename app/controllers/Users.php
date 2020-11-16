<?php 

class Users extends Controller {
    public function __construct(){
        $this->userModel = $this->model('User');
    }

    public function register(){
        // Check for POST
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Process form

            //Sanitize Post data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

             // Init data
             $data = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'name_error' => '',
                'email_error' => '',
                'password_error' => '',
                'confirm_password_error' => ''
            ];

            //Validate Email
            if(empty($data['email'])){
                $data['email_error'] = 'Please enter email';
            } else {
                // Check email
                if($this->userModel->findUserByEmail($data['email'])){
                    $data['email_error'] = 'User exists';
                }
            }

            //Validate name
            if(empty($data['name'])){
                $data['name_error'] = 'Please enter name';
            }

            //Validate Password
            if(empty($data['password'])){
                $data['password_error'] = 'Please enter password';
            } elseif(strlen($data['password']) < 6){
                $data['password_error'] = 'The password must be longer than 6 characters';
            }

            //Validate Confirm Password
            if(empty($data['confirm_password'])){
                $data['confirm_password_error'] = 'Please re enter password';
            } else{
                if($data['password'] != $data['confirm_password']){
                $data['password_error'] = 'The password must be longer than 6 characters';
                $data['confirm_password_error'] = 'Passwords do not match';
            }
        }

        //Make sure errors are empty
        if(empty($data['email_error']) && empty($data['name_error']) && empty($data['password_error'])
        && empty($data['confirm_password_error'])){
            //Validated
           
            //Hash Password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            //Register User
            if($this->userModel->register($data)){
                flash('register_success', 'You are registered and can log in');
                redirect('users/login');
            } else {
                die('Something went wrong');
            }

        } else {
            //Load view errors
            $this->view('users/register', $data); 
        }

        } else {
            // Init data
            $data = [
                'name' => '',
                'email' => '',
                'password' => '',
                'confirm_password' => '',
                'name_error' => '',
                'email_error' => '',
                'password_error' => '',
                'confirm_password_error' => ''
            ];

            //Load view
            $this->view('users/register', $data);
        }
    }

    public function login(){
        // Check for POST
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Process form
         //Sanitize Post data
         $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

         // Init data
         $data = [
            'email' => trim($_POST['email']),
            'password' => trim($_POST['password']),
            'email_error' => '',
            'password_error' => '',
        ];

          //Validate Email
          if(empty($data['email'])){
            $data['email_error'] = 'Please enter email';
        }

           //Validate Password
           if(empty($data['password'])){
            $data['password_error'] = 'Please enter password';
        }

            //Check for user/email
            if($this->userModel->findUserByEmail($data['email'])){
                //User found
            } else {
                $data['email_error'] = 'User or Password incorrect';
            }

        if(empty($data['email_error']) &&  empty($data['password_error'])){
            //Validated
           // Check and set logged in user
           $loggedInUser = $this->userModel->login($data['email'], $data['password']);

           if($loggedInUser){
            // create Session
                $this->createUserSession($loggedInUser);
           } else {
               $data['password_error'] = 'User or Password incorrect';

               $this->view('users/login', $data);
           }
        } else {
            //Load view errors
            $this->view('users/login', $data); 
        }

        } else {
            // Init data
            $data = [
                'email' => '',
                'password' => '',
                'confirm_password' => '', 
                'email_error' => '',
                'password_error' => '',
            ];

            //Load view
            $this->view('users/login', $data);
        }
    }

    public function createUserSession($user){
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->name;
        redirect('posts/index');
    }

    public function logout(){
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);
        session_destroy();
        redirect('users/login');
    }

  
}