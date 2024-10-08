CREATE TABLE `b01_DataStore`.`TaskIO` ( 
`AttemptId` TEXT NOT NULL , 
`SubjectId` TEXT NULL DEFAULT NULL ,
`FieldSize` INT NULL DEFAULT NULL ,
`SessionId` INT NULL DEFAULT NULL ,
`TrialId` TEXT NULL DEFAULT NULL ,
`PairId` INT NULL DEFAULT NULL ,
`TrialType` TEXT NULL DEFAULT NULL , 
`OppId` INT NULL DEFAULT NULL , 
`FieldIdx_A` INT NULL DEFAULT NULL , 
`FieldIdx_B` INT NULL DEFAULT NULL , 
`FieldIdx_C` INT NULL DEFAULT NULL , 
`AttemptNum` INT NULL DEFAULT NULL ,
`FieldIdx_R` INT NULL DEFAULT NULL ,
`Correct` BOOLEAN NULL DEFAULT NULL ,
`RT` INT NULL DEFAULT NULL , 
`DateTime_Write` DATETIME NULL DEFAULT NULL , 
PRIMARY KEY (`AttemptId`(32))) ENGINE = MyISAM;