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
    <p>The values are case sensitive.</p>

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

    <hr />

    <h2>Find the score for the regular home games where the Lakers won:</h2>
    <p>SELECT tname, home_pts <br>
        FROM Team_Info, Regular <br>
        WHERE tname = 'Lakers' and home_tname = tname and home_pts > away_pts</p>
    <form method="GET" action="admin.php">
        <!--refresh page when submitted-->
        <input type="hidden" id="selectTupleRequest" name="selectTupleRequest">
        <input type="submit" name="selectTuples"></p>
    </form>

    <hr />

    <h2> Find the what the average amount of points each team scored at home:</h2>
    <p>SELECT home_tname, avg(home_pts)<br>
        FROM Regular<br>
        GROUP BY home_tname</p>
    <form method="GET" action="admin.php">
        <!--refresh page when submitted-->
        <input type="hidden" id="avgTupleRequest" name="avgTupleRequest">
        <input type="submit" name="avgTuples"></p>
    </form>

    <hr />

    <h2> Find which team has the player with the lowest shooting percentage:</h2>
    <p>SELECT a.tname, a.avg_shooting_perc<br>
    FROM (SELECT tname, avg_shooting_perc = avg(shooting_perc)<br>
    &emsp;&ensp;FROM Player_Stats_Only<br>
    &emsp;&ensp;GROUP BY tname<br>
    ) AS a<br>
    WHERE a.avg_shooting_perc = (SELECT min(avg_shooting_perc) FROM (SELECT tname, avg(shooting_perc) avg_shooting_perc FROM Player_Stats_Only GROUP BY tname)) </p>
    <form method="GET" action="admin.php">
        <!--refresh page when submitted-->
        <input type="hidden" id="leastTupleRequest" name="leastTupleRequest">
        <input type="submit" name="leastTuples"></p>
    </form>

    <hr />

    <h2> Find which team has the player with the lowest shooting percentage (With view):</h2>
    <p>WITH a(tname, avg_shooting_perc) as (SELECT tname, avg(shooting_perc) avg_shooting_perc FROM Player_Stats_Only GROUP BY tname)<br>
    SELECT a.tname , a.avg_shooting_perc<br>
    FROM a<br>
    WHERE a.avg_shooting_perc = (SELECT min(avg_shooting_perc) FROM a)</p>
    <form method="GET" action="admin.php">
        <!--refresh page when submitted-->
        <input type="hidden" id="leastTupleRequest" name="leastTupleRequest">
        <input type="submit" name="leastViewTuples"></p>
    </form>

    <hr />

    <h2> Find all the games that were officiated by all referees with more than 20 years of experience：</h2>
    <p> SELECT gid FROM Regular r<br>
    WHERE NOT EXISTS (( SELECT Referee.rid FROM Referee WHERE yearsExperience > 20)<br>
    MINUS<br>
    (SELECT mr.rid<br>
    FROM moderate_Regular mr <br>
    WHERE mr.gid = r.gid)</p>
    <form method="GET" action="admin.php">
        <!--refresh page when submitted-->
        <input type="hidden" id="divTupleRequest" name="divTupleRequest">
        <input type="submit" name="divTuples"></p>
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
            stname		 	char(50),
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

        echo "<br> creating inserts <br>";
        executePlainSQL("INSERT INTO Stadium VALUES(1, 'Madison Square Garden', 20789, 'New York')");
        executePlainSQL("INSERT INTO Stadium VALUES(2, 'Crypto.com Arena', 20000, 'Los Angeles')");
        executePlainSQL("INSERT INTO Stadium VALUES(3, 'United Center', 23500, 'Chicago')");
        executePlainSQL("INSERT INTO Stadium VALUES(4, 'UWells Fargo Center', 19500, 'Philadelphia')");
        executePlainSQL("INSERT INTO Stadium VALUES(5, 'Footprint Center', 18422, 'Phoenix')");

        executePlainSQL("INSERT INTO Coach VALUES('Tom Thibodeau', 4375000)");
        executePlainSQL("INSERT INTO Coach VALUES('Frank Vogel', 4000000)");
        executePlainSQL("INSERT INTO Coach VALUES('Doc Rivers', 12365000)");
        executePlainSQL("INSERT INTO Coach VALUES('Billy Donovan', 5000000)");
        executePlainSQL("INSERT INTO Coach VALUES('Monty Williams', 10000000)");

        executePlainSQL("INSERT INTO Team_Location VALUES('New York', 'East')");
        executePlainSQL("INSERT INTO Team_Location VALUES('Los Angeles', 'West')");
        executePlainSQL("INSERT INTO Team_Location VALUES('Chicago', 'East')");
        executePlainSQL("INSERT INTO Team_Location VALUES('Philadelphia', 'East')");
        executePlainSQL("INSERT INTO Team_Location VALUES('Phoenix', 'West')");

        executePlainSQL("INSERT INTO Team_Info VALUES('Knicks', 'Tom Thibodeau', 1,  TO_DATE('1968-01-01', 'YYYY-MM-DD'), 'New York', 1946, 'Scott Perry')");
        executePlainSQL("INSERT INTO Team_Info VALUES('Lakers', 'Frank Vogel', 2,  TO_DATE('1999-01-01', 'YYYY-MM-DD'), 'Los Angeles', 1947, 'Rob Pelinka')");
        executePlainSQL("INSERT INTO Team_Info VALUES('Bulls', 'Billy Donovan', 3,  TO_DATE('1994-01-01', 'YYYY-MM-DD'), 'Chicago', 1966, 'Marc Eversley')");
        executePlainSQL("INSERT INTO Team_Info VALUES('76ers', 'Doc Rivers',4 ,TO_DATE('1996-01-01', 'YYYY-MM-DD'), 'Philadelphia', 1946, 'Daryl Morey')");
        executePlainSQL("INSERT INTO Team_Info VALUES('Suns', 'Monty Williams', 5, TO_DATE('1992-01-01', 'YYYY-MM-DD'), 'Phoenix', 1968, 'James Jones')");

        executePlainSQL("INSERT INTO Player_Team_Name VALUES('Bulls', 11, 'Demar Derozan')");
        executePlainSQL("INSERT INTO Player_Team_Name VALUES('76ers', 21, 'Joel Embiid')");
        executePlainSQL("INSERT INTO Player_Team_Name VALUES('Lakers', 6, 'Lebron James')");
        executePlainSQL("INSERT INTO Player_Team_Name VALUES('Suns', 3, 'Chris Paul')");
        executePlainSQL("INSERT INTO Player_Team_Name VALUES('Knicks', 9, 'RJ Barrett')");

        executePlainSQL("INSERT INTO Player_Stats_Only VALUES(1,11,'Lakers',6,4,41180544,'$0.00','Front','American',37,52,206)");
        executePlainSQL("INSERT INTO Player_Stats_Only VALUES(2,12,'Suns',3,4,30800000,'$0.00','Back','American',33,49,183)");
        executePlainSQL("INSERT INTO Player_Stats_Only VALUES(3,13,'76ers',21,5,31579390,'$0.00','Front','American',28, 49, 213)");
        executePlainSQL("INSERT INTO Player_Stats_Only VALUES(4,14, 'Bulls',11,3,26000000,'$0.00','Front','American',32,52,198)");
        executePlainSQL("INSERT INTO Player_Stats_Only VALUES(5,15, 'Knicks',9,3,8623920,'$0.00','Front','Canadian',21,42,198)");

        executePlainSQL("INSERT INTO Injury VALUES(1, 'Knee Soreness', 3, 'Knee')");
        executePlainSQL("INSERT INTO Injury VALUES(1, 'Back Spasms', 3,'Back')");
        executePlainSQL("INSERT INTO Injury VALUES(3, 'Ankle Sprain', 4, 'Ankle')");
        executePlainSQL("INSERT INTO Injury VALUES(2, 'Back Soreness', 4,'Back')");
        executePlainSQL("INSERT INTO Injury VALUES(5, 'Neck Soreness', 2, 'Neck')");

        executePlainSQL("INSERT INTO Referee VALUES(1,5,54)");
        executePlainSQL("INSERT INTO Referee VALUES(2,5,67)");
        executePlainSQL("INSERT INTO Referee VALUES(3,26,47)");
        executePlainSQL("INSERT INTO Referee VALUES(4,13,36)");
        executePlainSQL("INSERT INTO Referee VALUES(5,14,74)");
        executePlainSQL("INSERT INTO Referee VALUES(6,7,43)");
        executePlainSQL("INSERT INTO Referee VALUES(7,10,22)");
        executePlainSQL("INSERT INTO Referee VALUES(8,11,11)");
        executePlainSQL("INSERT INTO Referee VALUES(9,28,48)");

        executePlainSQL("INSERT INTO Regular VALUES(1,1,'Knicks','Lakers',TO_DATE('2022-03-02', 'YYYY-MM-DD'),111,98,1)");
        executePlainSQL("INSERT INTO Regular VALUES(2,2,'Lakers','Bulls',TO_DATE('2022-02-28', 'YYYY-MM-DD'),123,88,0)");
        executePlainSQL("INSERT INTO Regular VALUES(3,3,'Bulls','76ers', TO_DATE('2021-10-31', 'YYYY-MM-DD'),108,97,0)");
        executePlainSQL("INSERT INTO Regular VALUES(4,4,'76ers','Suns', TO_DATE('2021-11-22', 'YYYY-MM-DD'),122,125,0)");
        executePlainSQL("INSERT INTO Regular VALUES(5,5,'Suns','Knicks',TO_DATE('2022-01-15', 'YYYY-MM-DD'),98,125,0)");

        executePlainSQL("INSERT INTO Playoff VALUES(6,4,'76ers','Knicks',TO_DATE('2022-05-19', 'YYYY-MM-DD'),123,81,1,2)");
        executePlainSQL("INSERT INTO Playoff VALUES(7,5,'Suns','Lakers',TO_DATE('2022-05-21', 'YYYY-MM-DD'),130,84,1,3)");
        executePlainSQL("INSERT INTO Playoff VALUES(8,3,'Bulls','76ers',TO_DATE('2022-06-17', 'YYYY-MM-DD'),99,97,2,4)");
        executePlainSQL("INSERT INTO Playoff VALUES(9,1,'76ers','Knicks',TO_DATE('2022-05-12', 'YYYY-MM-DD'),101,113,1,7)");
        executePlainSQL("INSERT INTO Playoff VALUES(10,2,'Suns','Lakers',TO_DATE('2022-05-22', 'YYYY-MM-DD'),118,112,1,6)");

        executePlainSQL("INSERT INTO moderate_Regular VALUES(1,1)");
        executePlainSQL("INSERT INTO moderate_Regular VALUES(2,2)");
        executePlainSQL("INSERT INTO moderate_Regular VALUES(3,3)");
        executePlainSQL("INSERT INTO moderate_Regular VALUES(3,9)");
        executePlainSQL("INSERT INTO moderate_Regular VALUES(4,4)");
        executePlainSQL("INSERT INTO moderate_Regular VALUES(5,5)");

        executePlainSQL("INSERT INTO moderate_Playoff VALUES(6,1)");
        executePlainSQL("INSERT INTO moderate_Playoff VALUES(7,2)");
        executePlainSQL("INSERT INTO moderate_Playoff VALUES(8,6)");
        executePlainSQL("INSERT INTO moderate_Playoff VALUES(9,7)");
        executePlainSQL("INSERT INTO moderate_Playoff VALUES(10,8)");

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

    function handleSelectRequest()
    {
        global $db_conn;

        $result = executePlainSQL("SELECT tname, home_pts FROM Team_Info, Regular WHERE tname = 'Lakers' and home_tname = tname and home_pts > away_pts");

        echo "<table>";
        echo "<tr><th>Team Name</th><th>&emsp;Points</th></tr>";

        while (($row = OCI_Fetch_Array($result, OCI_BOTH)) != false) {
            echo "<tr><td>" . $row[0] . "</td><td>&emsp;" . $row[1] . "</td></tr>"; //or just use "echo $row[0]"
        }

        echo "</table>";
        oci_free_statement($result);
    }

    function handleAvgRequest()
    {
        global $db_conn;

        $result = executePlainSQL("SELECT home_tname, avg(home_pts) FROM Regular GROUP BY home_tname");

        echo "<table>";
        echo "<tr><th>Team Name</th><th>&emsp;Average Home Points</th></tr>";

        while (($row = OCI_Fetch_Array($result, OCI_BOTH)) != false) {
            echo "<tr><td>" . $row[0] . "</td><td>&emsp;" . $row[1] . "</td></tr>"; //or just use "echo $row[0]"
        }

        echo "</table>";
        oci_free_statement($result);
    }

    function handleLeastRequest()
    {
        global $db_conn;

        $result = executePlainSQL("SELECT tname, avg(shooting_perc) as avg_shooting_perc FROM Player_Stats_Only GROUP BY tname");

        $result = executePlainSQL("SELECT a.tname , a.avg_shooting_perc
        FROM (SELECT tname, avg(shooting_perc) avg_shooting_perc FROM Player_Stats_Only GROUP BY tname) a
        WHERE a.avg_shooting_perc = (SELECT min(avg_shooting_perc) FROM (SELECT tname, avg(shooting_perc) avg_shooting_perc FROM Player_Stats_Only GROUP BY tname))");

        echo "<table>";
        echo "<tr><th>Team Name</th><th>&emsp;Average Shooting Percentage</th></tr>";

        while (($row = OCI_Fetch_Array($result, OCI_BOTH)) != false) {
            echo "<tr><td>" . $row[0] . "</td><td>&emsp;" . $row[1] . "</td></tr>"; //or just use "echo $row[0]"
        }

        echo "</table>";
        oci_free_statement($result);
    }

    function handleLeastViewRequest()
    {
        global $db_conn;

        $result = executePlainSQL("WITH a(tname, avg_shooting_perc) as (SELECT tname, avg(shooting_perc) avg_shooting_perc FROM Player_Stats_Only GROUP BY tname)
        SELECT a.tname , a.avg_shooting_perc
         FROM a
         WHERE a.avg_shooting_perc = (SELECT min(avg_shooting_perc) FROM a)");

        echo "<table>";
        echo "<tr><th>Team Name</th><th>&emsp;Average Shooting Percentage</th></tr>";

        while (($row = OCI_Fetch_Array($result, OCI_BOTH)) != false) {
            echo "<tr><td>" . $row[0] . "</td><td>&emsp;" . $row[1] . "</td></tr>"; //or just use "echo $row[0]"
        }

        echo "</table>";
        oci_free_statement($result);
    }

    function handleDivRequest()
    {
        global $db_conn;

        $result = executePlainSQL("SELECT gid FROM Regular r WHERE NOT EXISTS (
            (SELECT rf.rid FROM Referee rf WHERE rf.yearsExperience > 20)
            MINUS
            (SELECT mr.rid FROM moderate_Regular mr WHERE mr.gid = r.gid))");

        echo "<table>";
        echo "<tr><th>Game ID</th></tr>";

        while (($row = OCI_Fetch_Array($result, OCI_BOTH)) != false) {
            echo "<tr><td>" . $row[0] . "</td></tr>"; //or just use "echo $row[0]"
        }

        echo "</table>";
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
            if (array_key_exists('selectTuples', $_GET)) {
                handleSelectRequest();
            }
            if (array_key_exists('avgTuples', $_GET)) {
                handleAvgRequest();
            }
            if (array_key_exists('leastTuples', $_GET)) {
                handleLeastRequest();
            }
            if (array_key_exists('leastViewTuples', $_GET)) {
                handleLeastViewRequest();
            }
            if (array_key_exists('divTuples', $_GET)) {
                handleDivRequest();
            }

            disconnectFromDB();
        }
    }

    if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit'])) {
        handlePOSTRequest();
    } else if (isset($_GET['countTupleRequest']) || isset($_GET['selectTupleRequest'])
     || isset($_GET['avgTupleRequest']) || isset($_GET['leastTupleRequest']) || isset($_GET['divTupleRequest'])) {
        handleGETRequest();
    }
    ?>
</body>

</html>