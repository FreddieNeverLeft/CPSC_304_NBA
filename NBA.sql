DROP TABLE Player_Stats_Only cascade constraints;
DROP TABLE Player_Team_Name cascade constraints;
DROP TABLE Team_Location cascade constraints;
DROP TABLE Team_Info cascade constraints;
DROP TABLE Injury cascade constraints;
DROP TABLE Referee cascade constraints;
DROP TABLE moderate_Regular cascade constraints;
DROP TABLE moderate_Playoff cascade constraints;
DROP TABLE Regular cascade constraints;
DROP TABLE Playoff cascade constraints;
DROP TABLE Stadium cascade constraints;
DROP TABLE Coach cascade constraints;

CREATE TABLE Stadium 
(
stid 			int			PRIMARY KEY, 		
stname		 	char(20),
capacity 		int,
stlocation		char(50)		UNIQUE
);
grant select on Stadium to public;

CREATE TABLE Coach 
(
cname 		char(30)		PRIMARY KEY, 		
salary	 		int
);
grant select on Coach to public;

CREATE TABLE Team_Location
(
city		char(20),
conference	char(20)	NOT NULL,
PRIMARY KEY(city)
);
grant select on Team_Location to public;

CREATE TABLE Team_Info 
(
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
);
grant select on Team_Info to public;

CREATE TABLE Player_Team_Name
(
tname		char(20),
num	int,
pname		char(30)	NOT NULL,
PRIMARY KEY(tname, num),
FOREIGN KEY(tname) REFERENCES Team_Info 
);
grant select on Player_Team_Name to public;

CREATE TABLE Player_Stats_Only 
(
pid int PRIMARY KEY, 
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
FOREIGN KEY(tname, num) REFERENCES Player_Team_Name ON DELETE CASCADE
);
grant select on Player_Stats_Only to public;

CREATE TABLE Injury 
(
pid 			int, 
iname 			char(30), 
severity		int, 		
body_area 		char(30), 
PRIMARY KEY(pid, iname),
FOREIGN KEY(pid) REFERENCES Player_Stats_Only ON DELETE CASCADE
);
grant select on Injury to public;

CREATE TABLE Referee 
(
rid 			int			PRIMARY KEY, 		
yearsExperience 	int,
num 		int
);
grant select on Referee to public;

CREATE TABLE Regular 
(
gid 			int, 
stid 			int 			NOT NULL,
home_tname 		char(20)		NOT NULL,
away_tname	 	char(20)		NOT NULL, 
gdate			date, 
home_pts		int, 
away_pts		int,		
divisionalGame	int,        /*True:1, False:0*/
PRIMARY KEY(gid),
FOREIGN KEY(home_tname) REFERENCES Team_Info,
FOREIGN KEY(away_tname) REFERENCES Team_Info,
FOREIGN KEY(stid) REFERENCES Stadium
);
grant select on Regular to public;

CREATE TABLE Playoff 
(
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
);
grant select on Playoff to public;

CREATE TABLE moderate_Regular
(
gid 		int, 
rid		    int,  		 
PRIMARY KEY(gid, rid),
FOREIGN KEY(gid) REFERENCES Regular,
FOREIGN KEY(rid) REFERENCES Referee
);
grant select on moderate_Regular to public;

CREATE TABLE moderate_Playoff
(
gid 	int, 
rid		int,  		 
PRIMARY KEY(gid, rid),
FOREIGN KEY(gid) REFERENCES Playoff,
FOREIGN KEY(rid) REFERENCES Referee
);
grant select on moderate_Playoff to public;


INSERT INTO Stadium VALUES(1, 'Madison Square Garden', 20789, 'New York');
INSERT INTO Stadium VALUES(2, 'Crypto.com Arena', 20000, 'Los Angeles');
INSERT INTO Stadium VALUES(3, 'United Center', 23500, 'Chicago');
INSERT INTO Stadium VALUES(4, 'UWells Fargo Center', 19500, 'Philadelphia');
INSERT INTO Stadium VALUES(5, 'Footprint Center', 18422, 'Phoenix');

INSERT INTO Coach VALUES('Tom Thibodeau', 4375000);
INSERT INTO Coach VALUES('Frank Vogel', 4000000);
INSERT INTO Coach VALUES('Billy Donovan', 5000000);
INSERT INTO Coach VALUES('Doc Rivers', 12365000);
INSERT INTO Coach VALUES('Monty Williams', 10000000);

INSERT INTO Team_Location VALUES('New York', 'East');
INSERT INTO Team_Location VALUES('Los Angeles', 'West');
INSERT INTO Team_Location VALUES('Chicago', 'East');
INSERT INTO Team_Location VALUES('Philadelphia', 'East');
INSERT INTO Team_Location VALUES('Phoenix', 'West');

INSERT INTO Team_Info VALUES('Knicks', 'Tom Thibodeau', 1,  1968, 'New York', 1946, 'Scott Perry');
INSERT INTO Team_Info VALUES('Lakers', 'Frank Vogel', 2,  1999, 'Los Angeles', 1947, 'Rob Pelinka');
INSERT INTO Team_Info VALUES('Bulls', 'Billy Donovan', 3,  1994, 'Chicago', 1966, 'Marc Eversley');
INSERT INTO Team_Info VALUES('76ers', 'Doc Rivers',4 ,1996, 'Philadelphia', 1946, 'Daryl Morey');
INSERT INTO Team_Info VALUES('Suns', 'Monty Williams', 5, 1992, 'Phoenix', 1968, 'James Jones');

INSERT INTO Player_Team_Name VALUES('Bulls', 11, 'Demar Derozan');
INSERT INTO Player_Team_Name VALUES('76ers', 21, 'Joel Embiid');
INSERT INTO Player_Team_Name VALUES('Lakers', 6, 'Lebron James');
INSERT INTO Player_Team_Name VALUES('Phoenix', 3, 'Chris Paul');
INSERT INTO Player_Team_Name VALUES('Knicks', 9, 'RJ Barrett');


INSERT INTO Player_Stats_Only VALUES(1,11,'Lakers',6,4,41180544,'$0.00','Front','American',37,52,206);
INSERT INTO Player_Stats_Only VALUES(2,12,'Suns',3,4,30800000,'$0.00','Back','American',33,49,183);
INSERT INTO Player_Stats_Only VALUES(3,13,'76ers',21,5,31579390,'$0.00','Front','American',28, 49, 213);
INSERT INTO Player_Stats_Only VALUES(4,14, 'Bulls',11,3,26000000,'$0.00','Front','American',32,52,198);
INSERT INTO Player_Stats_Only VALUES(5,15, 'Knicks',9,3,8623920,'$0.00','Front','Canadian',21,42,198);


INSERT INTO Injury VALUES(1, 'Knee Soreness', 3, 'Knee');
INSERT INTO Injury VALUES(1, 'Back Spasms', 3,'Back');
INSERT INTO Injury VALUES(3, 'Ankle Sprain', 4, 'Ankle');
INSERT INTO Injury VALUES(2, 'Back Soreness', 4,'Back');
INSERT INTO Injury VALUES(5, 'Neck Soreness', 2, 'Neck');

INSERT INTO Referee VALUES(1,5,54);
INSERT INTO Referee VALUES(2,5,67);
INSERT INTO Referee VALUES(3,26,47);
INSERT INTO Referee VALUES(4,13,36);
INSERT INTO Referee VALUES(5,14,74);
INSERT INTO Referee VALUES(6,7,43);
INSERT INTO Referee VALUES(7,10,22);
INSERT INTO Referee VALUES(8,11,11);
INSERT INTO Referee VALUES(9,28,48);

INSERT INTO Regular VALUES(1,1,'Knicks','Nets','2022-03-02',111,98,1);
INSERT INTO Regular VALUES(2,2,'Lakers','Rockets','2022-02-28',123,88,0);
INSERT INTO Regular VALUES(3,3,'Bulls','Celtics','2021-10-31',108,97,0);
INSERT INTO Regular VALUES(4,4,'76ers','Warriors','2021-11-22',122,125,0);
INSERT INTO Regular VALUES(5,5,'Suns','Jazz','2022-01-15',98,125,0);

INSERT INTO Playoff VALUES(6,6,'76ers','Knicks','2022-05-19',123,81,1,2);
INSERT INTO Playoff VALUES(7,7,'Sunss','Lakers','2022-05-21',130,84,1,3);
INSERT INTO Playoff VALUES(8,8,'Bulls','76ers','2022-06-17',99,97,2,4);
INSERT INTO Playoff VALUES(9,9,'76ers','Knicks','2022-05-12',101,113,1,7);
INSERT INTO Playoff VALUES(10,10,'Suns','Lakers','2022-05-42',118,112,1,6);

INSERT INTO moderate_Regular VALUES(1,1);
INSERT INTO moderate_Regular VALUES(2,2);
INSERT INTO moderate_Regular VALUES(3,3);
INSERT INTO moderate_Regular VALUES(3,9);
INSERT INTO moderate_Regular VALUES(4,4);
INSERT INTO moderate_Regular VALUES(5,5);

INSERT INTO moderate_Playoff VALUES(6,1);
INSERT INTO moderate_Playoff VALUES(7,2);
INSERT INTO moderate_Playoff VALUES(8,6);
INSERT INTO moderate_Playoff VALUES(9,7);
INSERT INTO moderate_Playoff VALUES(10,18);


























