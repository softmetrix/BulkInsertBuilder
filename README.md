## BulkInsertBuilder
#### Description
Generates and executes bulk insert query. Adjustable bulk insert chunk size.

#### Usage 

	use Softmetrix\BulkInsertBuilder\BulkInsertBuilder;

    $dsn = 'mysql:host=localhost;dbname=sampledb';
    $username = 'dbusr1';
    $password = 'dbpass123';
    $table = 'inserttest';
    $bulkInsertBuilder = new BulkInsertBuilder($dsn, $username, $password, $table);
    $data = [];
    for ($i = 0; $i < 5500; ++$i) {
        $data[] = [
            'field1' => rand(1, 1000),
            'field2' => rand(1, 1000),
            'field3' => rand(1, 1000),
        ];
    }
    $bulkInsertBuilder->bulkInsert($data);
    	  
