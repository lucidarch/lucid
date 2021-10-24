<p align="center"><a href="https://lucidarch.dev" target="_blank"><img src="https://raw.githubusercontent.com/lucidarch/artwork/main/logo.jpg" width="400"></a></p>

<p align="center" style="margin-left: -20px">
    <a href="https://docs.lucidarch.dev"><img src="http://img.shields.io/badge/read_the-docs-2196f3.svg" alt="Documentation"></a>
    <a href="https://lucid-slack.herokuapp.com"><img src="https://lucid-slack.herokuapp.com/badge.svg" alt="Slack Chat"/></a>
    <a href="https://github.com/lucidarch/lucid/actions?query=workflow%3Atests"><img src="https://github.com/lucidarch/lucid/workflows/tests/badge.svg" alt="Build Status"></a>
    <a href="https://packagist.org/packages/lucidarch/lucid"><img src="https://img.shields.io/packagist/v/lucidarch/lucid" alt="Latest Stable Version"></a>
    <a href="https://github.com/lucidarch/lucid/blob/main/LICENSE"><img src="https://img.shields.io/packagist/l/lucidarch/lucid" alt="License"></a>
</p>

---

- Website: https://lucidarch.dev
- Documentation: https://docs.lucidarch.dev
- Social: we share updates & interesting content from the web
    - Twitter: [@lucid_arch](https://twitter.com/lucid_arch) & [#lucidarch](https://twitter.com/search?q=%23lucidarch)
    - Reddit: [/r/lucidarch](https://www.reddit.com/r/lucidarch/)

## Table of Contents

  * [About Lucid](#about-lucid)
  * [Concept](#concept)
    * [Table of Contents](#table-of-contents)
  * [Position](#position)
  * [The Stack](#the-stack)
    * [Framework](#framework)
    * [Foundation](#foundation)
    * [Domains](#domains)
    * [Services](#services)
    * [Features](#features)
    * [Operations](#operations)
    * [Data](#data)
  * [Benefits](#benefits)
    * [Organization](#organization)
    * [Reuse &amp; Replace](#reuse--replace)
    * [Boundaries](#boundaries)
    * [Multitenancy](#multitenancy)
* [Contribute](#contribute)
  * [Bug &amp; Issue Reports](#bug--issue-reports)
  * [Support Questions](#support-questions)
  * [Core Development Discussion](#core-development-discussion)
  * [Which Branch? And How To Contribute](#which-branch-and-how-to-contribute)
    * [Setup for Development](#setup-for-development)
  * [Security Vulnerabilities](#security-vulnerabilities)
  * [Coding Style](#coding-style)
    * [PHPDoc](#phpdoc)
  * [Code of Conduct](#code-of-conduct)


## About Lucid
Lucid is a software architecture to build scalable Laravel projects. It incorporates **Command Bus** and **Domain Driven Design**
at the core, upon which it builds a stack of directories and classes to organize business logic.
It also derives from **SOA (Service Oriented Architecture)** the notion of encapsulating functionality
within a service and enriches the concept with more than the service being a class.

**Use Lucid to:**

- Write clean code effortlessly
- Protect your code from deterioriting over time
- Review code in fractions of the time typically required
- Incorporate proven practices and patterns in your applications
- Navigate code and move between codebases without feeling astranged

## Concept

This architecture is in an amalgamation of best practices, design patterns and proven methods.


- **Command Bus**: to dispatch units of work. In Lucid terminology these units will be a `Feature`, `Job` or `Operation`.
- **Domain Driven Design**: to organize the units of work by categorizing them according to the topic they belong to.
- **Service Oriented Architecture**: to encapsulate and manage functionalities of the same purpose with their required resources (routes, controllers, views, datatbase migrations etc.)

If you prefer a video, watch the announcement at Laracon EU 2016:

<p align="center">
    <a href="http://www.youtube.com/watch?v=wSnM4JkyxPw">
        <img src="http://img.youtube.com/vi/wSnM4JkyxPw/0.jpg">
    </a>
</p>

---

### Table of Contents

- [Position](#position)
- [The Stack](#the-stack)
    - [Framework](#framework)
    - [Foundation](#foundation)
    - [Domains](#domains)
    - [Services](#services)
    - [Features](#features)
    - [Data](#data)
- [Benefits](#benefits)
    - [Organization](#organization)
    - [Reuse & Replace](#reuse--replace)
    - [Boundaries](#boundaries)
    - [Multitenancy](#multitenancy)

## Position

In a typical MVC application, Lucid will be the bond between the application's entrypoints and the units that do the work,
securing code form meandring in drastic directions:

![Lucid MVC Position](https://raw.githubusercontent.com/lucidarch/artwork/main/material/concept/mvc-position.png)

## The Stack

At a glance...

![Lucid Stack](https://raw.githubusercontent.com/lucidarch/artwork/main/material/concept/stack.png)

### Framework

Provides the "kernel" to do the heavy lifting of the tedious stuff such as request/response lifecycle, dependency
injection, and other core functionalities.

### Foundation

Extends the framework to provide higher level abstractions that are custom to the application and can be shared
across the entire stack rather than being case-specific.

Examples of what could go into foundation are:
- `DateTime` a support class for common date and time functions
- `JsonSerializableInterface` that is used to identify an object to be serializable from and to JSON format

### Domains

Provide separation to categorize jobs and corresponding classes that belong to the same topic. A domain operates in isolation
from other domains and exposes its functionalities to features and operations through Lucid jobs only.

Consider the structure below for an example on what a domain may look like:

```
app/Domains/GitHub
├── GitHubClient
├── Jobs
│   ├── FetchGitHubRepoInfoJob
│   └── LoginWithGitHubJob
├── Exceptions
│   ├── InvalidTokenException
│   └── RepositoryNotFoundException
└── Tests
    └── GitHubClientTest
    └── Jobs
        ├── FetchGitHubReposJobTest
        └── LoginWithGitHubJobTest
```

[documentation](https://docs.lucidarch.dev/domains/) contains more details on working with domains.

### Services

Are directories rich in functionality, used to separate a [Monolith]({{<ref "/micro-vs-monolith/#monolith">}}) into
areas of focus in a multi-purpose application.

Consider the example of an application where we enter food recipes and would want our members to have discussions in a forum,
we would have two services: *1) Kitchen, 2) Forum* where the kitchen would manage all that's related to recipes, and forum is obvious:

```
app/Services
├── Forum
└── Kitchen
```

and following is a single service's structure, highlighted are the Lucid specific directories:

<pre>
app/Services/Forum
├── Console
│   └── Commands
├── <strong>Features</strong>
├── <strong>Operations</strong>
├── Http
│   ├── Controllers
│   └── Middleware
├── Providers
│   ├── KitchenServiceProvider
│   ├── BroadcastServiceProvider
│   └── RouteServiceProvider
├── <strong>Tests</strong>
│   └── <strong>Features</strong>
│   └── <strong>Operations</strong>
├── database
│   ├── factories
│   ├── migrations
│   └── seeds
├── resources
│   ├── lang
│   └── views
└── routes
    ├── api
    ├── channels
    ├── console
    └── web
</pre>

[documentation](https://docs.lucidarch.dev/services/) has more examples of services and their contents.

### Features

Represent a human-readable application feature in a class. It contains the logic that implements the feature but with the least
amount of detail, by running jobs from domains and operations at the application or service level.

Serving the Feature class will be the only line in a controller's method (in MVC), consequently achieving the thinnest form of controllers.

```php
class AddRecipeFeature extends Feature
{
    public function handle(AddRecipe $request)
    {
        $price = $this->run(CalculateRecipePriceOperation::class, [
            'ingredients' => $request->input('ingredients'),
        ]);

        $this->run(SaveRecipeJob::class, [
            'price' => $price,
            'user' => Auth::user(),
            'title' => $request->input('title'),
            'ingredients' => $request->input('ingredients'),
            'instructions' => $request->input('instructions'),
        ]);

        return $this->run(RedirectBackJob::class);
    }
}
```

[documentation](https://docs.lucidarch.dev/features/) about features expands on how to serve them as classes from anywhere.

### Operations

Their purpose is to increase the degree of code reusability by piecing jobs together to provide composite functionalities from across domains.

```php
class NotifySubscribersOperation extends Operation
{
    private int $authorId;

    public function __construct(int $authorId)
    {
        $this->authorId = $authorId;
    }

    /**
     * Sends notifications to subscribers.
     *
     * @return int Number of notification jobs enqueued.
     */
    public function handle(): int
    {
        $author = $this->run(GetAuthorByIDJob::class, [
            'id' => $this->authorId,
        ]);

        do {

            $result = $this->run(PaginateSubscribersJob::class, [
                'authorId' => $this->authorId,
            ]);

            if ($result->subscribers->isNotEmpty()) {
                // it's a queueable job so it will be enqueued, no waiting time
                $this->run(SendNotificationJob::class, [
                    'from' => $author,
                    'to' => $result->subscribers,
                    'notification' => 'article.published',
                ]);
            }

        } while ($result->hasMorePages());

        return $result->total;
    }
}
```

[documentation](https://docs.lucidarch.dev/operations/) goes over this simple yet powerful concept.

### Data

For a scalable set of interconnected data elements, we've created a place for them in `app/Data`,
because most likely over time writing the application there could develop a need for more than Models in data,
such as Repositories, Value Objects, Collections and more.

```
app/Data
├── Models
├── Values
├── Collections
└── Repositories
```

## Benefits

There are valuable advantages to what may seem as overengineering.

### Organization

- Predictable impact of changes on the system when reviewing code
- Reduced debugging time since we’re dividing our application into isolated areas of focus (divide and conquer)
- With Monolith, each of our services can have their own versioning system (e.g. Api service is at v1 while Chat is at v2.3 yet reside)
yet reside in the same codebase

### Reuse & Replace

By dissecting our application into small building blocks of code - a.k.a units - we've instantly opened the door for a high
degree of code sharing across the application with Data and Domains, as well as replaceability with the least amount of friction
and technical debt.

### Boundaries

By setting boundaries you would've taken a step towards proetcting application code from growing unbearably large
and made it easier for new devs to onboard. Most importantly, that you've reduced technical debt to the minimum so that you don't
have to pay with bugs and sleepless nights; code doesn't run on good intentions nor wishes.

### Multitenancy

When our application scales we'd typically have a bunch of instances of it running in different locations,
at some point we would want to activate certain parts of our codebase in some areas and shut off others.

Here’s a humble example of running *Api*, *Back Office* and *Web App* instances of the same application, which in Lucid terminology
are *services* that share functionality through *data* and *domains*:

![Lucid multitenancy](https://raw.githubusercontent.com/lucidarch/artwork/main/material/concept/multitenancy.jpeg)

# Contribute

## Bug & Issue Reports

To encourage active collaboration, Lucid strongly encourages contribution through [pull requests](#which-branch-and-how-to-contribute).
"Bug reports" may be searched or created in [issues](https://github.com/lucidarch/lucid/issues) or sent in the form of a [pull request](#which-branch-and-how-to-contribute) containing a failing test or steps to reproduce the bug.

If you file a bug report, your issue should contain a title and a clear description of the issue. You should also include as much relevant information as possible and a code sample that demonstrates the issue. The goal of a bug report is to make it easy for yourself - and others - to replicate the bug and develop a fix.

⏱  PRs and issues are usually checked about three times a week so there is a high chance yours will be picked up soon.

The Lucid Architecture source code is on GitHub as [lucidarch/lucid](https://github.com/lucidarch/lucid).

## Support Questions

Lucid Architecture's GitHub issue trackers are not intended to provide help or support. Instead, use one of the following channels:

- [Discussions](https://github.com/lucidarch/lucid/discussions) is where most conversations takes place
- For a chat hit us on our official [Slack workspace](https://lucid-slack.herokuapp.com/) in the `#support` channel
- If you prefer StackOverflow to post your questions you may use [#lucidarch](https://stackoverflow.com/questions/tagged/lucidarch) to tag them

## Core Development Discussion

You may propose new features or improvements of existing Lucid Architecture behaviour in the [Lucid Discussins](https://github.com/lucidarch/lucid/discussions).
If you propose a new feature, please be willing to implement at least some of the code that would be needed to complete the feature, or collaborate on active ideation in the meantime.

Informal discussion regarding bugs, new features, and implementation of existing features takes place in the `#internals` channel of the [Lucid Slack workspace](https://lucid-slack.herokuapp.com/).
Abed Halawi, the maintainer of Lucid, is typically present in the channel on weekdays from 8am-5pm EEST (Eastern European Summer Time), and sporadically present in the channel at other times.

## Which Branch? And How To Contribute

The `main` branch is what contains the latest live version and is the one that gets released.

- Fork this repository
- Clone the forked repository to where you'll edit your code
- Create a branch for your edits (e.g. `feature/queueable-units`, `fix/issue-31`)
- Commit your changes and their tests (if applicable) with meaningful short messages
- Push your branch `git push origin feature/queueable-units`
- Open a [PR](https://github.com/lucidarch/lucid/compare) to the `main` branch, which will run tests for your edits

⏱ PRs and issues are usually checked about three times a week.


### Setup for Development

Following are the steps to setup for development on Lucid:

> Assuming we're in `~/dev` directory...

- Clone the forked repository `[your username]/lucid` which will create a `lucid` folder at `~/dev/lucid`
- Create a Laravel project to test your implementation in it `composer create-project laravel/laravel myproject`
- Connect the created Laravel project to the local Lucid installation; in the Laravel project's `composer.json`
    ```json
    "require": {
        "...": "",
        "lucidarch/lucid": "@dev"
    },
    "repositories": [
        {
            "type": "path",
            "url": "~/dev/lucid",
            "options": {
                "symlink": true
            }
        }
    ],
    "minimum-stability": "dev",
    ```
> Make sure you change the `url` to the absolute path of your directory

- Run `composer update` to create the symlink

Now all your changes in the lucid directory will take effect automatically in the project.

## Security Vulnerabilities

If you discover a security vulnerability within Lucid, please send an email to Abed Halawi at [halawi.abed@gmail.com](mailto:halawi.abed@gmail.com).
All security vulnerabilities will be promptly addressed.

## Coding Style

Lucid Architecture follows the [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding standard and the [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) autoloading standard.

### PHPDoc

Below is an example of a valid Lucid Architecture documentation block. Note that the `@param` attribute is followed by two spaces, the argument type, two more spaces, and finally the variable name:

```php
/**
 * Register a binding with the container.
 *
 * @param  string|array  $abstract
 * @param  \Closure|string|null  $concrete
 * @param  bool  $shared
 * @return void
 *
 * @throws \Exception
 */
public function bind($abstract, $concrete = null, $shared = false)
{
    //
}
```

## Code of Conduct

The Lucid Architecture code of conduct is derived from the Laravel code of conduct. Any violations of the code of conduct may be reported to Abed Halawi (halawi.abed@gmail.com):

- Participants will be tolerant of opposing views.
- Participants must ensure that their language and actions are free of personal attacks and disparaging personal remarks.
- When interpreting the words and actions of others, participants should always assume good intentions.
- Behavior that can be reasonably considered harassment will not be tolerated.
