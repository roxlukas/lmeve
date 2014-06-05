ALTER TABLE `apicontactlist`
  DROP PRIMARY KEY,
   ADD PRIMARY KEY(
     `contactID`,
     `corporationID`);