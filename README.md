# [API Platform](https://api-platform.com) X [Vulcain](https://vulcain.rocks)

This is a demo project using [API Platform](https://api-platform.com), [Vulcain](https://vulcain.rocks) and Varnish all together!
It adds Vulcain support to [the API that I created during SymfonyCon 2018](https://github.com/dunglas/symfonycon-lisbon).

To start the API, Varnish and Vulcain, download this project and run:

    $ docker-compose -f docker-compose.prod.yml up

The API is now available on `https://localhost:8443`.

To execute the JavaScript benchmarks, go to:

    * https://localhost:8443/test-min-waterfall.html (maximum waterfall effect)
    * https://localhost:8443/test-max-waterfall.html (as fast as possible)

Results will appear in the console! These files are stored in the [`api/public/`](api/public/) directory.

A benchmark using Symfony HTTP Client is also available, but you need to edit a line in the `vendor` directory (to fix a bug)
to make it working, see the comment at the top of [`test-http-client.php`](test-http-client.php)!

    $ php test-http-client.php

To reproduce this project in a raw API Platform project, follow this guide!

## Install API Platform + Vulcain

    $ curl -L https://github.com/api-platform/api-platform/archive/master.tar.gz -o api-platform-master.tar.gz
    $ tar xzvf api-platform-master.tar.gz
    $ cd api-platform-master
    # Master version has optimizations for Vulcain
    $ docker-compose exec php composer req api-platform/core:dev-master

## Configure the API to be Fully Normalized

```yaml
# api/config/packages/api_platform.yaml
api_platform:
    defaults:
        normalization_context:
            iri_only: true
    # ...
```

## Generate the Entities

### Conference

*  `name` (`string`, not nullable)

    docker-compose exec php bin/console make:entity --api-resource Conference    
    
     New property name (press <return> to stop adding fields):
     > name
    
     [select the default answer for all other questions]

### Session

* `conference` (to-one relation with `Conference`, not nullable)
* `author` (`text`, not nullable)
* `title` (`string`, not nullable)
* `summary` (`text`)

    docker-compose exec php bin/console make:entity Session --api-resource
    
     New property name (press <return> to stop adding fields):
     > title
    
     Add another property? Enter the property name (or press <return> to stop adding fields):
     > summary
    
     Field type (enter ? to see all types) [string]:
     > text
    
     Can this field be null in the database (nullable) (yes/no) [no]:
     > yes
 
     Add another property? Enter the property name (or press <return> to stop adding fields):
     > author
    
     Add another property? Enter the property name (or press <return> to stop adding fields):
     > conference
    
     Field type (enter ? to see all types) [string]:
     > ManyToOne
    
     What class should this entity be related to?:
     > Conference
    
     Is the Session.conference property allowed to be null (nullable)? (yes/no) [yes]:
     > no
    
     A new property will also be added to the Conference class so that you can access the related Session objects from it.
    
     New field name inside Conference [sessions]:
     > sessions

## Feedback

* `session` (to-one relation with `Session`, not nullable)
* `comment` (`text`, not nullable)
* `rating` (`smallint`, not nullable)

    docker-compose exec php bin/console make:entity Feedback --api-resource    
    
     New property name (press <return> to stop adding fields):
     > comment
    
     Field type (enter ? to see all types) [string]:
     > text
    
     Add another property? Enter the property name (or press <return> to stop adding fields):
     > rating
    
     Field type (enter ? to see all types) [string]:
     > smallint    
    
     Add another property? Enter the property name (or press <return> to stop adding fields):
     > session
    
     Field type (enter ? to see all types) [string]:
     > ManyToOne
    
     What class should this entity be related to?:
     > Session
    
     Is the Feedback.session property allowed to be null (nullable)? (yes/no) [yes]:
     > no

## Update the DB

    docker-compose exec php bin/console doctrine:schema:update --force

## Add Fixtures

     docker-compose exec php composer req --dev alice

```yaml
# api/fixtures/data.yaml
App\Entity\Conference:
    conference_{1..10}:
        name: '<catchPhrase()>'

App\Entity\Session:
    session_{1..100}:
        conference: '@conference_*'
        title: '<catchPhrase()>'
        summary: '<sentences(3, true)>'
        author: '<name()>'

App\Entity\Feedback:
    feedback_{1..100}:
        session: '@session_*'
        comment: '<sentences(3, true)>'
        rating: '<numberBetween(0, 5)>'
```

     docker-compose exec php bin/console hautelook:fixtures:load 

## The JS!

See `api/public/test-max-waterfall.html` and `api/public/test-min-waterfall.html`.

# Setting Up the Prod Mode

See `docker-compose.prod.yml`.

## Credits

Created by [Kévin Dunglas](https://dunglas.fr).
