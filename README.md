# Agile Dashboard - Community Edition
Dashboard with agile metrics connected to Atlassian Jira.

![Sample Dashboard](http://metrics.rgies.de/agile-dashboard3.jpg)

## Requirements

* PHP >= 5.5.9 (with php5-curl, php5-mcrypt)
* Composer
* Mysql

## How to install

1. Download and install **[Composer](http://getcomposer.org/download)**.

2. Clone or [download](https://github.com/rgies/agile-dashboard/archive/master.zip) the repository:

		$ git clone https://github.com/rgies/agile-dashboard.git
		$ cd agile-dashboard

## How to configure

1. Start setup and follow the instuctions:

		$ composer install

2. Create database table

		$ app/console doctrine:database:create
    
3. Update database schema

		$ app/console doctrine:schema:update --force
		
4. Load initial data

        $ app/console doctrine:fixture:load
    
    **Note:**
    Default login credentials (admin/admin). Please login an change password.    
    
# Usage

## Table of Contents

### Setup
- [Setup service provider](#setup-service-provider)

### Development
- [Develop own widgets](#develop-own-widgets)    
    
#### Setup service provider

1. Login as Administrator (Default: admin/admin)

2. Go to the "Service Provider" section

3. Click on Jira and put in the login credentials

#### Develop own widgets

1. Create widget code skeleton

        $ app/console dashboard:generate:widget
        
2. Add generated widget name to config 

    _app/config/widget.yml_

        # Widget plugins
        widget_plugins:
            'MyWidgetBundle': 'My widget name'

3. Insert view and controller code

    _Resources/views/Default/widget.html.twig_
    
        {% block widget_body %}
            <!-- ADD HERE YOUR WIDGET CONTENT -->
            ...
        {% endblock %}

    _Controller/DefaultController.php_
    
        // ======================================================
        // INSERT HERE YOUR CODE TO COLLECT THE NEEDED DATA
        // ======================================================
        $response = array(
            'value' => $insert_here_your_data
        );
        // ======================================================

4. Optional change data model

    1. Adapt entity file _Entity/WidgetConfig.php_
    
    2. Adapt form file _Form/WidgetConfigType.php_

5. Update database schema

		$ app/console doctrine:schema:update --force
