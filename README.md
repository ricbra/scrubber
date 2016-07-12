scubber
-------

POC implementation of a configurable tool to scrub sensitive information from a database. Doctrine DBAL is used for data
retrieval and storage. Faker is used for obfuscating the data.

# How to use

    $ docker-compose up -d
    
Then, assuming you have a local PHP installation:

    $ php scubber.php
    

