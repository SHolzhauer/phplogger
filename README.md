PHPLogger is a php class that can be used to add logging capabilities to
your php application.

# Destinations
PHPLogger can send the events to a couple of different endpoints.
1. Console
2. [AWS Firehose](https://aws.amazon.com/kinesis/data-firehose/)
3. [AWS CloudWatch](https://aws.amazon.com/cloudwatch/)
4. Elasticsearch
5. Logstash

# Configuration
The class uses the ini file provided for its configuration.

# Usage
Using the class is as simple as including it and creating a class object:
```php
include_once . '/path/to/Logger.php';
$logging = new Logging();
```
