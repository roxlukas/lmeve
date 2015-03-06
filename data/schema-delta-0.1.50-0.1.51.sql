-- schema delta for northbound api

ALTER TABLE  `lmnbapi` ADD `userID` INT NOT NULL DEFAULT 0 AFTER `apiKeyID`;

