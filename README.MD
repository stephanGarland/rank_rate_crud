
# Rank Rate

* Description: CRUD framework for entering, storing, and visualizing rankings and ratings for employees.

* Usage:
    1. Use Chrome.
    2. In fit_login.php, replace the Base64 chunk on lines 69-74 with an array of authorized users in format 'First_Name' => 'GHR'. If you don't care about the obfuscation, remove the base64_decode.
    3. The second base64_decode (and its sister in index.php on line 3) are currently not implemented.
    4. Create the three required databases; review_employees, review_mbo, and review_ratings. See file db_create for required SQL statements. Note that you'll need to edit review_mbo to match your desired MBOs.
    5. Acquire permissions for or create your own database, save login info in db.php as $db.
    6. Edit getMBO.php's SELECT to match review_mbo.
    7. Edit saveNewRecord.php, $group_1 and/or $group_2 variables to match whatever boilerplate you want for a writeup.

* Depends: 
    * jQuery-3.3.1
    * Bootstrap-3.3.7
    * ag-Grid-18.1
    * Plotly
    * Animate.css (https://daneden.github.io/animate.css/)
    * PHP
    * PostgreSQL
