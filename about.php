<?php
session_start();
if (!isset($_COOKIE['rank_rate'])) {
    header("Location: http://ccdev/cc/rank_rate/rankRateLogin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta charset="utf-8">
        <script src="/library/mermaid-8.1.0.min.js"></script>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
        <title>About CLNS Rank/Rate</title>
        <style type="text/css">
            @media screen and (min-width: 1400px) {
                html {
                    font-family: 'Open Sans', sans-serif;
                    max-width: 1200px;
                    margin: auto;
                    scroll-behavior: smooth;
                }

                #navbar {
                  position: fixed;
                  left: 0;
                  margin: 5px;
                  z-index: 9999;
                  background-color: #337ab7;
                  border-radius: 5px;
                }

                #navbar a {
                    float: left;
                    display: block;
                    color: white;
                    text-align: center;
                    padding: 14px;
                    text-decoration: none;
                }

                h3 {
                    margin-left: 10px;
                }

                p {
                    margin-left: 10px;
                }
            }
        </style>
    </head>
    <body>
    <div id="navbar">
        <a href="#top">Top of Page</a><br>
        <a href="#loginFlow">Login Flow</a><br>
        <a href="#viewFlow">View Flow</a></br>
        <a href="#editFlow">Edit Flow</a><br>
        <a href="#summaryFlow">Summary Flow</a><br>
        <a href="#about">About/HOWTO</a><br>
        <a href="#addSups">Add Supervisors</a><br>
        <a href="#changeMBO">Change MBOs</a><br>
        <a href="#bugs">Known Bugs/TODO</a><br>
        <a href="#sundry">Technical Sundries</a><br>
        <a href="#contact">Contact Me</a><br>
        <a href="index.php">Back to App</a>
    </div>

        <h1 id="top">Rank/Rate Documentation</h1>

        <h2>Overview</h2>

        <p>Built using Bootstrap3, Bootbox, jQuery, jQuery UI, Mermaid (this page only), Plotly, Tabulator, and ag-Grid, along with some CSS niceties.
            Not IE compatible. Use Chrome.</p>

        <p>PostgreSQL backend, PHP/JS frontend with the above libraries/frameworks.</p>

        <h2 id="loginFlow">Login flow</h2>
        <div class="mermaid">
            sequenceDiagram
                participant db as db.php
                participant index as index.php
                participant login as fit_login.php
                participant rrl as rankRateLogin.php

            Note left of db: Postgres login info
            index -->> login: Check for session cookie
            alt cookie not set
                index ->> rrl: Force to rankRateLogin.php
            else cookie set
                index ->> index: Load DOM
            end

            rrl ->> login: POST FIT auth
            login -->> login: Check for auth
            alt incorrect FIT
                login->> rrl: Error and return
            else correct FIT
                login-->> login: Check if user allowed
                alt user allowed
                    login->>index: Set session cookie and return
                else user not allowed
                    login->>rrl: Error and return
                end
            end
        </div>

        <h2 id="viewFlow">View flow</h2>
        <div class="mermaid">
            sequenceDiagram
                participant index as main.js
                participant sups as getSups.php
                participant emp_ent as getEmpEntries.php
                participant create as create.js
                participant snr as saveNewRecord.php
                participant reports as getReports.php
                participant emp_det as getEmpDetails.php
                participant ghrs as getGHRs.php
                participant get_mbo as getMBO.php

            index->>sups: GET sups for Select Sup button
            reports->>index: POST selected sup, quarter, year to call function to fill Tabulator, refresh on any parameter change
            emp_ent->>index: GET employee listing for sup
            Note over index, emp_ent: Lists any emp ever reported to sup
            index->>snr: Bulk add allows for templated db entries for entire shift, does not overwrite existing
            Note left of index: Qtr/Year required

            Note over create: Shows current sup
            index->>create: Read/Update launches page to edit specific employee
            ghrs->>create: GET GHRs for assigned employees of given sup/qtr/year for bulk add
            emp_det->>create: GET specific employee details for given qtr/year
            emp_ent->>create: GET and filter name, GHR, etc.
            get_mbo->>create: GET MBO template for specific employee title for use in modal
        </div>

        <h2 id="editFlow">Edit flow</h2>
        <div class="mermaid">
            sequenceDiagram
                participant index as main.js
                participant create as create.php
                participant del as deleteRecord.php
                participant sp as savePage.php
                participant ssr as saveShiftRank.php
                participant sw as saveWriteup.php
                participant save_mbo as saveMBO.php

            index->>ssr: main.js has modal with draggable shift rankings, once set, POST to saveShiftRank.php to save in db
            create->>del: Delete an individual record
            create->>sp: POST items on individual page like rating, EANs, etc.
            create->>sw: POST templated writeup from modal containing freeform text and MBO justification
            create->>save_mbo: POST from modal utilizing tabulator to calculate MBO score
        </div>

        <h2 id="summaryFlow">Summary flow</h2>
        <div class="mermaid">
            sequenceDiagram
                participant index as main.js
                participant get_sum as getSummary.php
                participant plot as Plotly
                participant sql as Postgres
                participant table as ag-Grid

            index->>get_sum: GET with selected sup
            sql->>get_sum: Get employee details (ranking, rating,EAN items, etc.) with inline SQL
            loop Continually
                get_sum-->>table: Check for filters and rebuild table
                table-->>plot: Check for table rebuild and rebuild plot
            end
            table->>get_sum: Launch modal for employee containing writeup
        </div>

        <p>
        <h2 id="about">About/HOWTO</h2>

        <h3 id="addSups">Add new supervisors</h3>

        <p>To add new supervisors, they first must exist in the SQL table as either a Supervisor or TR. In the future,
            this should pull from a central table, but for now, you have to update review_employees manually.
            Additionally, they have to have at least one employee listed as reporting to them
            (by reports_to_name only, but odd things could happen if you don't update reports_to [GHR] as well)
            in review_ratings. The query is doing a join on review_employees.full_name to review_ratings.reports_to_name,
            so ensure the two match.
        </p>

        <p>Next, you'll need to add them as an authorized user in fit_login.php. Find the array, add the desired person.
            As an aside, index.php is just checking that a session cookie exists with title 'rank_rate.'
            That should probably look for the FIT token or something similarly robust. The cookie clears when the browser closes,
            not the tab.
        </p>

        <h3 id="changeMBO">Change MBOs</h3>

        <p>The easiest way is via "PostgreSQL table update.xlsm"</p>
        <ol>
            <li>On the Parameters sheet, insert the DB name and login information, and select REPLACE or ADD for Type.
                <strong>NOTE: REPLACE IMMEDIATELY AND WITHOUT FURTHER WARNING OVERWRITES THE SELECTED DATABASE,
                    SO ENSURE YOU HAVE A BACKUP FIRST.
                </strong>
            </li>
            <li>On the Data sheet, insert your desired columns as headers in Row 1, then records below.</li>
            <li>Run the macro.</li>
            <li>Check the result, both in the DB and in the app, specifically in the MBO Calc modal to ensure it comes across correctly.
                I think I've gotten all special characters to parse correctly, but also maybe you don't need emojis and
                Zalgo text in your MBOs or writeups, mkay?
            </li>
        </ol>

        <h3 id="bugs">Known bugs/TODO</h3>

        <ol>
            <li>In ag-Grid filtering, you must use 't' and 'f' to filter booleans; nothing else works.</li>
            <li>The bulk add does a SQL query to find the last quarter/year for that supervisor, and templates
                new entries off of that. If a supervisor loses someone during that time, it will need to be
                manually edited using the SQL editor of your choice. A future possibility is a GUI for a very simple
                manual SQL edit functionality to the table, with a separate admin login.
            </li>
            <li>An annual roll-up that somehow more neatly displays everyone's employees.</li>
            <li>Along the same lines, storing and displaying individual MBO scores rather than a total would be nice.</li>
            <strike><li>Debating whether or not to allow direct edits from within ag-Grid; currently it is read-only.</li></strike><strong>EPHEMERAL EDITS ALLOWED</strong></li>
            <li>I've done some refactoring to clean up code, use more const/let and less var, etc. It could use more work.</li>
            <strike><li>JS should be pulled out of the HTML and added as an include. Eventually. </strike><strong>DONE</strong></li>
            <li>Rebuild the entire project using React or Angular. Yeah.</li>
        </ol>

        <h3 id="sundry">Technical sundries</h3>

        <ul>
            <li>The app does its best to assume the user is incompetent or malicious,
                and warns them if something is destructive or potentially problematic.
            </li>
            <li>All SQL queries are prepared.
            </li>
            <li>I'm pretty sure there are some vulnerabilities, but I didn't spend a
                lot of time fuzzing it given the limited and internal userbase.
            </li>
            <li>Build dev DBs if you're going to try to break it. db_create.sql can do this for you.
            </li>
        </ul>

        <br>
        <br>
        <br>
        <h3 style="margin-left: 0px !important" id="contact">Contact Me</h3>
        <a href="mailto:s.garland@samsung.com">s.garland@samsung.com</a>
        <script>mermaid.initialize({startOnLoad:true});</script>
    </body>
</html>
