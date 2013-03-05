<?php

class SiteController extends Controller
{
	public $layout='column1';

	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$headers="From: {$model->email}\r\nReply-To: {$model->email}";
				mail(Yii::app()->params['adminEmail'],$model->subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		if (!defined('CRYPT_BLOWFISH')||!CRYPT_BLOWFISH)
			throw new CHttpException(500,"This application requires that PHP was compiled with Blowfish support for crypt().");

		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}

	/**
	 * registration
	 */
	public function actionReg()
	{
		if (Yii::app()->user->isGuest) {
			$user = new user;

			$this->performAjaxValidation($user);

			if(empty($_POST['User'])) {
				$this->render('reg', array('model' => $user));
			}
			else {
				$user->attributes = $_POST['User'];
				if($user->validate('reg')) {
					if($user->model()->count("email = :email",
						array(':email' => $user->email))) {
						$user->addError('email', 'this email is already registered');
						$this->render("reg", array('model' => $user));
					}
					else if($user->model()->count("Username = :Username", array(':Username' => $user->username))) {
						$user->addError('Username', 'this username is already registered');
						$this->render("reg", array('model' => $user));
					}
					else{
						$user->password = $user->hashPassword($user->password);
						if($user->save()) {
								$this->render("reg", array('regOk' => 'register complite'));
						}
						else {
								throw new CHttpException(403, 'Erorr db');
						}
					}
				}
				else {

					$this->render('reg', array('model' => $user));
				}
			}
		}
		else {
			$this->redirect(Yii::app()->user->returnUrl);
		}
	}

	protected function performAjaxValidation($model) {
		if(isset($_POST['ajax']) && $_POST['ajax']==='user-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	/**
	 * forum
	 */
	public function actionForum()
	{
		// display forum
		$this->render('forum');
	}
	
	//Authenticate with forum vanilla
	public function actionVanillaProxy()
	{
		if (!Yii::app()->user->isGuest){
			$user =User::model()->findByPk(Yii::app()->user->id);
			echo "
					UniqueID={$user->id}
					Name={$user->username}
					Email={$user->email}
				";
		}
		else {
			$this->render('VanillaProxy');
		}
	}
}
