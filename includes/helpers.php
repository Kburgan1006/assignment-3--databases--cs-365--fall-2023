<?php
require_once "config.php";

#Uses given input ot search attributes for given search. Uses separate function for
#password searches.
function search($search, $search_param) {
    try {
        $db = new PDO(
            "mysql:host=" . DBHOST . "; dbname=" . DBNAME . ";charset=utf8",
            DBUSER,
            DBPASS
        );

        #Sets up encryption for PDO instance
        $db -> exec("SET block_encryption_mode = 'aes-256-cbc'");
        $db -> exec("SET @key_str = " . key_str);
        $db -> exec("SET @init_vector = " . init_vector);

        #Checks search param so it can decrypt column, if not regularly searches.
        if($search_param == "Password"){
            $select_query = "SELECT passwords.Registration_id, Website_Name, Website_URL, User_Name, Email_address, CAST(AES_DECRYPT(passwords.Password_, @key_str, @init_vector) AS CHAR(512)), Comment, Creation_Time FROM passwords CROSS JOIN registration WHERE registration.Registration_id = passwords.Registration_id AND CAST(AES_DECRYPT(Password_, @key_str , @init_vector) AS CHAR(512)) LIKE \"%{$search}%\"";
            $statement = $db -> prepare($select_query);
            $statement -> execute();
        } else {
            $select_query = "SELECT passwords.Registration_id, Website_Name, Website_URL, User_Name, Email_address, CAST(AES_DECRYPT(passwords.Password_, @key_str, @init_vector) AS CHAR(512)), Comment, Creation_Time FROM passwords CROSS JOIN registration WHERE registration.Registration_id = passwords.Registration_id AND $search_param LIKE \"%{$search}%\"";
            $statement = $db -> prepare($select_query);
            $statement -> execute();
        }

        #Displays all results in table with labeled columns
        if (count($statement -> fetchAll()) == 0) {
            return 0;
        } else {
            echo "      <table>\n";
            echo "        <thead>\n";
            echo "          <tr>\n";
            echo "            <th>ID</th>\n";
            echo "            <th>Website Name</th>\n";
            echo "            <th>Website URL</th>\n";
            echo "            <th>User Name</th>\n";
            echo "            <th>Email Address</th>\n";
            echo "            <th>Password</th>\n";
            echo "            <th>Comment</th>\n";
            echo "            <th>Submission Time</th>\n";
            echo "          </tr>\n";
            echo "        </thead>\n";
            echo "        <tbody>\n";

            // Populate the table with data coming from the database...
            foreach ($db ->query($select_query) as $row) {
                echo "          <tr>\n";
                echo "            <td>" . htmlspecialchars($row[0]) . "</td>\n";
                echo "            <td>" . htmlspecialchars($row[1]) . "</td>\n";
                echo "            <td>" . htmlspecialchars($row[2]) . "</td>\n";
                echo "            <td>" . htmlspecialchars($row[3]) . "</td>\n";
                echo "            <td>" . htmlspecialchars($row[4]) . "</td>\n";
                echo "            <td>" . htmlspecialchars($row[5]) . "</td>\n";
                echo "            <td>" . htmlspecialchars($row[6]) . "</td>\n";
                echo "            <td>" . htmlspecialchars($row[7]) . "</td>\n";
                echo "          </tr>\n";
            }

            echo "         </tbody>\n";
            echo "      </table>\n";
        }

    } catch(PDOException $e) {
        echo '<p>The following message was generated by function <code>search</code>:</p>' . "\n";
        echo '<p id="error">' . $e -> getMessage() . '</p>' . "\n";

        exit;
    }
}

#Updates attribute based on search criteria
function update($current_attribute, $new_attribute, $query_attribute, $constraint) {
    try {
        $db = new PDO(
            "mysql:host=" . DBHOST . "; dbname=" . DBNAME . ";charset=utf8",
            DBUSER,
            DBPASS
        );

        #Sets up encryption for PDO instance
        $db -> exec("SET block_encryption_mode = 'aes-256-cbc'");
        $db -> exec("SET @key_str = " . key_str);
        $db -> exec("SET @init_vector = " . init_vector);

        #Different queries for different combinations of updates/searches using Password column
        if($query_attribute == 'Password' && $current_attribute == 'Password'){
            $db -> query("UPDATE passwords SET Password_ = AES_ENCRYPT(\"{$new_attribute}\", @key_str, @init_vector) WHERE CAST(AES_DECRYPT(Password_, @key_str, @init_vector) AS CHAR(512)) = \"{$constraint}\"");
        } elseif($query_attribute == 'Password'){
            $db -> query("UPDATE passwords SET {$current_attribute} = \"{$new_attribute}\" WHERE CAST(AES_DECRYPT(Password_, @key_str, @init_vector) AS CHAR(512)) = \"{$constraint}\"");
        }elseif($current_attribute == 'Password'){
            $db -> query("UPDATE passwords SET Password_ = AES_ENCRYPT(\"{$new_attribute}\", @key_str, @init_vector) WHERE {$query_attribute}=\"{$constraint}\"");
        } else {
            $db -> query("UPDATE passwords SET {$current_attribute}=\"{$new_attribute}\" WHERE {$query_attribute}=\"{$constraint}\"");
        }

    } catch(PDOException $e) {
        echo '<p>The following message was generated by function <code>update</code>:</p>' . "\n";
        echo '<p id="error">' . $e -> getMessage() . '</p>' . "\n";

        exit;
    }
}

#Inserts given user input into correct tables
function insert($Website_Name, $Website_URL, $Comment, $User_Name, $Email_Address, $Password_) {
    try {
        $db = new PDO(
            "mysql:host=" . DBHOST . "; dbname=" . DBNAME . ";charset=utf8",
            DBUSER,
            DBPASS
        );

        #Sets up encryption for PDO instance
        $db -> exec("SET block_encryption_mode = 'aes-256-cbc'");
        $db -> exec("SET @key_str = " . key_str);
        $db -> exec("SET @init_vector = " . init_vector);

        #Insert for registration table
        $statement = $db -> prepare("INSERT INTO registration VALUES (NULL, :Comment, now())");
        $statement -> execute(
            array(
                'Comment'           => $Comment,
            )
        );

        #Insert for passwords table
        $statement = $db -> prepare("INSERT INTO passwords VALUES (NULL, :Website_Name, :Website_URL, :User_Name, :Email_Address, AES_ENCRYPT(:Password_, @key_str, @init_vector), LAST_INSERT_ID())");
        $statement -> execute(
            array(
                'Website_Name'      => $Website_Name,
                'Website_URL'       => $Website_URL,
                'User_Name'         => $User_Name,
                'Email_Address'     => $Email_Address,
                'Password_'         => $Password_,
            )
        );

    } catch(PDOException $e) {
        echo '<p>The following message was generated by function <code>insert</code>:</p>' . "\n";
        echo '<p id="error">' . $e -> getMessage() . '</p>' . "\n";

        exit;
    }
}

#Deletes records by searching for correct record and then deleting both records using foreign key.
function delete($current_attribute, $constraint) {
    try {
        $db = new PDO(
            "mysql:host=" . DBHOST . "; dbname=" . DBNAME . ";charset=utf8",
            DBUSER,
            DBPASS
        );

        #Sets up encryption for PDO instance
        $db -> exec("SET block_encryption_mode = 'aes-256-cbc'");
        $db -> exec("SET @key_str = " . key_str);
        $db -> exec("SET @init_vector = " . init_vector);

        #Different query for password and others.
        if($current_attribute == "Password_")
        {
            $query = $db -> query("SELECT passwords.Registration_id FROM passwords JOIN registration WHERE passwords.Registration_id = registration.Registration_id AND CAST(AES_DECRYPT(passwords.Password_, @key_str, @init_vector) AS CHAR(512)), Comment, Creation_Time FROM passwords CROSS JOIN registration WHERE registration.Registration_id = passwords.Registration_id AND CAST(AES_DECRYPT(Password_, @key_str , @init_vector) AS CHAR(512)) =\"{$constraint}\"");
        } else{
            $query = $db -> query("SELECT passwords.Registration_id FROM passwords JOIN registration WHERE passwords.Registration_id = registration.Registration_id AND {$current_attribute}=\"{$constraint}\"");
        }

        #Fetches records
        $delete_id = $query->fetch(PDO::FETCH_ASSOC);

        #Confirms deletion or alerts user no records were found.
        if($delete_id == null){
            echo '<div id="error">No matching records found.</div>' . "\n";
            return 0;
        } else{
            $db -> query("DELETE FROM registration WHERE Registration_id = {$delete_id['Registration_id']}");
            echo '<div id="error">Succesfully Deleted.</div>' . "\n";
        }
    } catch(PDOException $e) {
        echo '<p>The following message was generated by function <code>delete</code>:</p>' . "\n";
        echo '<p id="error">' . $e -> getMessage() . '</p>' . "\n";

        exit;
    }
}
