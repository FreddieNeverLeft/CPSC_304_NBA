<html>

<head>
    <title>NBA Player Stats Admin</title>
</head>

<body>
    <h2>Reset</h2>
    <p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>

    <form method="POST" action="admin.php">
        <!-- if you want another page to load after the button is clicked, you have to specify that page in the action parameter -->
        <input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
        <p><input type="submit" value="Reset" name="reset"></p>
    </form>

    <hr />

    <h2>Insert Values into Coach</h2>
    <form method="POST" action="admin.php">
        <!--refresh page when submitted-->
        <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
        Name: <input type="text" name="cname"> <br /><br />
        Salary: &#36;<input type="text" name="salary"> <br /><br />

        <input type="submit" value="Insert" name="insertSubmit"></p>
    </form>

    <hr />

    <h2>Update Name in Coach</h2>
    <p>The values are case sensitive and if you enter in the wrong case, the update statement will not do anything.</p>

    <form method="POST" action="admin.php">
        <!--refresh page when submitted-->
        <input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
        Old Name: <input type="text" name="oldcName"> <br /><br />
        New Name: <input type="text" name="newcName"> <br /><br />

        <input type="submit" value="Update" name="updateSubmit"></p>
    </form>

    <hr />

    <h2>Count the Tuples in Coach</h2>
    <form method="GET" action="admin.php">
        <!--refresh page when submitted-->
        <input type="hidden" id="countTupleRequest" name="countTupleRequest">
        <input type="submit" name="countTuples"></p>
    </form>

    <?php
    //this tells the system that it's no longer just parsing html; it's now parsing PHP

    $success = True; //keep track of errors so it redirects the page only if there are no errors
    $db_conn = NULL; // edit the login credentials in connectToDB()
    $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

    function debugAlertMessage($message)
    {
        global $show_debug_alert_messages;

        if ($show_debug_alert_messages) {
            echo "<script type='text/javascript'>alert('" . $message . "');</script>";
        }
    }

    function executePlainSQL($cmdstr)
    { //takes a plain (no bound variables) SQL command and executes it
        //echo "<br>running ".$cmdstr."<br>";
        global $db_conn, $success;

        $statement = OCIParse($db_conn, $cmdstr);
        //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

        if (!$statement) {
            echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
            echo htmlentities($e['message']);
            $success = False;
        }

        $r = OCIExecute($statement, OCI_DEFAULT);
        if (!$r) {
            echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
            echo htmlentities($e['message']);
            $success = False;
        }

        return $statement;
    }

    function executeBoundSQL($cmdstr, $list)
    {
        /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
		See the sample code below for how this function is used */

        global $db_conn, $success;
        $statement = OCIParse($db_conn, $cmdstr);

        if (!$statement) {
            echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($db_conn);
            echo htmlentities($e['message']);
            $success = False;
        }

        foreach ($list as $tuple) {
            foreach ($tuple as $bind => $val) {
                //echo $val;
                //echo "<br>".$bind."<br>";
                OCIBindByName($statement, $bind, $val);
                unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
            }

            $r = OCIExecute($statement, OCI_DEFAULT);
            if (!$r) {
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
                echo htmlentities($e['message']);
                echo "<br>";
                $success = False;
            }
        }
    }

    function printResult($result)
    { //prints results from a select statement
        echo "<br>Retrieved data from table Coach:<br>";
        echo "<table>";
        echo "<tr><th>Name</th><th>Salary</th></tr>";

        while (($row = OCI_Fetch_Array($result, OCI_BOTH)) != false) {
            echo "<tr><td>" . $row[0] . "</td><td> $" . $row[1] . "</td></tr>"; //or just use "echo $row[0]"
        }

        echo "</table>";
    }

    function connectToDB()
    {
        global $db_conn;

        // Your username is ora_(CWL_ID) and the password is a(student number). For example,
        // ora_platypus is the username and a12345678 is the password.
        $db_conn = OCILogon("ora_yangyl17", "a24754350", "dbhost.students.cs.ubc.ca:1522/stu");

        if ($db_conn) {
            debugAlertMessage("Database is Connected");
            return true;
        } else {
            debugAlertMessage("Cannot connect to Database");
            $e = OCI_Error(); // For OCILogon errors pass no handle
            echo htmlentities($e['message']);
            return false;
        }
    }

    function disconnectFromDB()
    {
        global $db_conn;

        debugAlertMessage("Disconnect from Database");
        OCILogoff($db_conn);
    }

    function handleUpdateRequest()
    {
        global $db_conn;

        $old_name = $_POST['oldcName'];
        $new_name = $_POST['newcName'];

        // you need the wrap the old name and new name values with single quotations
        executePlainSQL("UPDATE coach SET cname='" . $new_name . "' WHERE cname='" . $old_name . "'");
        OCICommit($db_conn);

        $result = executePlainSQL("SELECT * FROM coach");
        printResult($result);
        
        oci_free_statement($result);
    }

    function handleResetRequest()
    {
        global $db_conn;
        // Drop old table
        executePlainSQL("DROP TABLE Player_Stats_Only cascade constraints");
        executePlainSQL("DROP TABLE Player_Team_Name cascade constraints");
        executePlainSQL("DROP TABLE Team_Location cascade constraints");
        executePlainSQL("DROP TABLE Team_Info cascade constraints");
        executePlainSQL("DROP TABLE Injury cascade constraints");
        executePlainSQL("DROP TABLE Referee cascade constraints");
        executePlainSQL("DROP TABLE moderate_Regular cascade constraints");
        executePlainSQL("DROP TABLE moderate_Playoff cascade constraints");
        executePlainSQL("DROP TABLE Regular cascade constraints");
        executePlainSQL("DROP TABLE Playoff cascade constraints");
        executePlainSQL("DROP TABLE Stadium cascade constraints");
        executePlainSQL("DROP TABLE Coach cascade constraints");

        // Create new table

        echo "<br> creating Stadium <br>";
        executePlainSQL("CREATE TABLE Stadium (
            stid 			int			PRIMARY KEY, 		
            stname		 	char(20),
            capacity 		int,
            stlocation		char(50)		UNIQUE
            )");

        echo "<br> creating Coach <br>";
        executePlainSQL("CREATE TABLE Coach (
            cname 		char(30)		PRIMARY KEY, 		
            salary	 		int
            )");

        echo "<br> creating Team_Location table <br>";
        executePlainSQL("CREATE TABLE Team_Location(
            city		char(20),
            conference	char(20)	NOT NULL,
            PRIMARY KEY(city)
            )");
        
        echo "<br> creating Team_Info table <br>";
        executePlainSQL("CREATE TABLE Team_Info (
            tname 			char(20)		PRIMARY KEY, 
            cname	 		char(30), 		
            stid 			int 			NOT NULL,
            stadium_since		date, 
            city			char(20),
            yearEstablished 	int			NOT NULL, 
            managerName	char(30)		NOT NULL,
            UNIQUE(stid), 	
            FOREIGN KEY(city) REFERENCES Team_Location,
            FOREIGN KEY(cname) REFERENCES Coach,
            FOREIGN KEY(stid) REFERENCES Stadium
            )");

        echo "<br> creating Player_Team_Name table <br>";
        executePlainSQL("CREATE TABLE Player_Team_Name(
            tname		char(20),
            num	int,
            pname		char(30)	NOT NULL,
            PRIMARY KEY(tname, num),
            FOREIGN KEY(tname) REFERENCES Team_Info 
            )");

        echo "<br> creating Player_Stats_Only table <br>";
        executePlainSQL("CREATE TABLE Player_Stats_Only (pid int PRIMARY KEY, 
            statid int UNIQUE,
            tname char(20),
            num int,
            contractYears	int,
            YearlySalary	int, 
            bonus		char(30),	
            Specialty 	char(50), 	
            nationality 	char(30) 	NOT NULL,
            age 		int 		NOT NULL,
            shooting_perc 	int, 
            height 		int 		NOT NULL,
            FOREIGN KEY(tname, num) REFERENCES Player_Team_Name)");

        echo "<br> creating Injury table <br>";
        executePlainSQL("CREATE TABLE Injury (
            pid 			int, 
            iname 			char(30), 
            severity		int, 		
            body_area 		char(30), 
            PRIMARY KEY(pid, iname),
            FOREIGN KEY(pid) REFERENCES Player_Stats_Only
            )");

        echo "<br> creating Referee table <br>";
        executePlainSQL("CREATE TABLE Referee (
            rid 			int			PRIMARY KEY, 		
            yearsExperience 	int,
            num 		int
            )");

        echo "<br> creating Regular <br>";
        executePlainSQL("CREATE TABLE Regular (
            gid 			int, 
            stid 			int 			NOT NULL,
            home_tname 		char(20)		NOT NULL,
            away_tname	 	char(20)		NOT NULL, 
            gdate			date, 
            home_pts		int, 
            away_pts		int,		
            divisionalGame	int, 
            PRIMARY KEY(gid),
            FOREIGN KEY(home_tname) REFERENCES Team_Info,
            FOREIGN KEY(away_tname) REFERENCES Team_Info,
            FOREIGN KEY(stid) REFERENCES Stadium
            )");

        echo "<br> creating Playoff <br>";
        executePlainSQL("CREATE TABLE Playoff (
            gid 			int, 
            stid 			int 			NOT NULL,
            home_tname 		char(20)		NOT NULL,
            away_tname	 	char(20)		NOT NULL, 
            gdate			date, 
            home_pts		int, 
            away_pts		int,
            round_num 		int, 
            game_num		int, 
            PRIMARY KEY(gid),
            FOREIGN KEY(home_tname) REFERENCES Team_Info,
            FOREIGN KEY(away_tname) REFERENCES Team_Info,
            FOREIGN KEY(stid) REFERENCES Stadium
            )");
        
        echo "<br> creating moderate <br>";
        executePlainSQL("CREATE TABLE moderate_Regular(
            gid 		int, 
            rid		int,  		 
            PRIMARY KEY(gid, rid),
            FOREIGN KEY(gid) REFERENCES Regular,
            FOREIGN KEY(rid) REFERENCES Referee
            )");
        
        executePlainSQL("CREATE TABLE moderate_Playoff(
            gid 	int, 
            rid		int,  		 
            PRIMARY KEY(gid, rid),
            FOREIGN KEY(gid) REFERENCES Playoff,
            FOREIGN KEY(rid) REFERENCES Referee
            )");

        OCICommit($db_conn);
    }

    function handleInsertRequest()
    {
        global $db_conn;

        //Getting the values from user and insert data into the table
        $tuple = array(
            ":bind1" => $_POST['cname'],
            ":bind2" => $_POST['salary']
        );

        $alltuples = array(
            $tuple
        );

        executeBoundSQL("insert into coach values (:bind1, :bind2)", $alltuples);
        OCICommit($db_conn);

        $result = executePlainSQL("SELECT * FROM coach");
        printResult($result);
        
        oci_free_statement($result);
    }

    function handleCountRequest()
    {
        global $db_conn;

        $result = executePlainSQL("SELECT Count(*) FROM coach");

        if (($row = oci_fetch_row($result)) != false) {
            echo "<br> The number of tuples in coach: " . $row[0] . "<br>";
        }
    }

    // HANDLE ALL POST ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handlePOSTRequest()
    {
        if (connectToDB()) {
            if (array_key_exists('resetTablesRequest', $_POST)) {
                handleResetRequest();
            } else if (array_key_exists('updateQueryRequest', $_POST)) {
                handleUpdateRequest();
            } else if (array_key_exists('insertQueryRequest', $_POST)) {
                handleInsertRequest();
            }

            disconnectFromDB();
        }
    }

    // HANDLE ALL GET ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handleGETRequest()
    {
        if (connectToDB()) {
            if (array_key_exists('countTuples', $_GET)) {
                handleCountRequest();
            }

            disconnectFromDB();
        }
    }

    if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit'])) {
        handlePOSTRequest();
    } else if (isset($_GET['countTupleRequest'])) {
        handleGETRequest();
    }
    ?>
</body>

</html>