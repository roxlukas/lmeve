--schema delta for new "successfulRuns" field in IndustryJobs endpoint

ALTER TABLE  `apiindustryjobs` ADD  `successfulRuns` INT NULL AFTER `completedSuccessfully` ;
ALTER TABLE  `apiindustryjobscrius` ADD  `successfulRuns` INT NULL AFTER `completedCharacterID` ;