# CodeForge 2.0

## About

CodeForge is a flexible code generating tool.

## Features

* Creating workspaces to make possibility to work with several entities at once.
* Generating models, views, controllers, sql schemas etc from description of the entities.
* Creating ready applications based on particular templates.
* Can be easy extended with extra features (generating custom form controls, widgets etc).
* Templates to build basic Yii application are included.

## Quick start

Look how it is easy:

### Step 1 - Create workspace

Run following command in work directory:

    sh > cf init php.yii.default
    
It creates workspace for basic Yii application.

### Step 2 - Add some entity

	sh > cf add Contact
	
then open `codeforge/src/Contact.model` and define some attributes:

    model Contact scheme mysql, php.yii.model, php.yii.controller.crud, php.yii.view.crud:
    
    	/// Contact name
    	required attr name char(200);
    	
    	/// Phone number
    	required attr phone char(200);
    
    	/// Email
    	required attr email email;
    	
    	/// Category
    	attr category enum("Family", "Friend", "Work");

### Step 3 - Add some preferences (optional):

    sh > cf add GlobalOptions
    
then open `codeforge/src/GlobalOptions.model` and fill it with something like this:

    model GlobalOption scheme php.yii.model.options, php.yii.view.options, php.yii.controller.options:
    
    	/// Site name
    	required attr sitename char(100);
    	
    	/// Tagline
    	required attr tagline text;
    	
### Step 4 - Build and release

	sh > cf build mysql php.yii.default
	
first time you need to answer several questions.
	
	sh > cf release -o www
	
where `www` is document root of your web application. Then you need to create SQL schema. All SQL scripts can be found in `www/data/sql`:

	sh > cd www/data/sql
	sh > cat *.sql | mysql -uuser -ppassword dbname
	
### Step 5 - Look at the result

Open in your browser `http://yourapp.local/index.php/admin/` and login using following credentials:

Username: `admin`

Password: `qwe`

I used `cf` as command, you also can create alias as follow:

    sh > alias cf="php /path/to/codeforge/codeforge.php"
    
Note that Yii framework is not included to template. You should put it to the same directory as `www`. Yii 1.1.14 should be used as framework.
