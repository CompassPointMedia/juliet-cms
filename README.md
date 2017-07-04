# README #

I'm working through the process of moving sites over to git and eventually an OOP platform.  This is Winged Republic and it was a pretty good instance of my Juliet Project CMS.  

### What is this repository for? ###

* Coding-only without /functions /components and /images/i and a few others which are better considered "vendor" features
* Version 0.2 (first one was Fantastic Shop)

### What do I need for this project? ###

* This repository at https://bitbucket.org/samfullman/temp-wingedrepublic or https://bitbucket.org/samfullman/juliet-cms
* Mod_rewrite
* Create a folder called `private` on the same level as the root folder, and copy `deploy/config.php` to that folder, and change settings accordingly.
* A database - in-progress dump of a database found in this repo.  A LOT of the views and tables are not needed.  Juliet was written for e-commerce.  Interesting legacies are the addr_ and finan_ tables which are a fairly good representation of both contacts and businesses.  Quickbooks was my original model for these tables.
* Connection(s) to the database(s) matching the `config.php` file.
* Pull down `/functions`, `/components`, and `/console`  which are on bitbucket.org/samfullman/packages/ respectively.
* Write access in the `site-local` and `images` folder
* Write access in the `pages` folder
* Get bitbucket.org/samfullman/assets/ `css`, `js`, and `ckeditor_3.4` into the `/Library folder`

Yes, I know we need a composer.json file for these dependencies!  I'm working on it, and there will be a journal and documentation of it in www.buildingthebatcave.com.

### What cool stuff is in this project? ###

Actually, a lot.  This is all pre-OOP days (for me) and incorporated:
 - login, logout, multiple permissions, logging
 - stats and visits, along the lines of what GA provides but "mine"
 - complete e-commerce (with a component on an SSL in RelateBase)
 - a very cool CMS editing system
 - my own file manager
 - image management including resizing and some further conceptual features
 - component creation - though this can all be fit into another framework with some thought - but I don't want to lose the leanness of coding that I had.
 - articles - including adding comments, sharing, to some degree multiple owners
 - an emailing system to rival iContact
 - a calendar and events feature
 - a little social networking stuff - only benefit is it was lean code
 - I think, use of my home-grown crumple.me URL shortening service
 - And, the actual node_hierarchy table system is pretty amazingly cool, and I think an atomic concept.
 
 
### Where is the project going from here? ###

This is version 0.2.  The objective is to have the site completely flexible based on the content in the CMS database, the /Library folder (which contains CSS), and the images folder.  That being the case, we're cetainly a long way from a very meaningful site as this layout would be good for 2006 but not today.  But if I can pull over my remaining Juliet sites using this code and have them functional immediately, then I've saved some time and I have a proof of concept to be moved into say a Laravel Framework.  Then I implement responsive templates, angular and a good backend upgrade and I'll have something perhaps worth taking to the next level.

* There are WAYYY too many global variables present.  That's going to take some work to implement - but I think this will be Laravel first, and globals localized to make it work as part of that


### Important points ###

* The code is based on the database being self-contained (on the old server all Juliet sites depended on a master database called relatebase_rfm
* Passwords are located in a file `../private/config.php` completely outside of the repo.  My decision has been to have passwords and sensitive information completely outside the folder structure of the repo.

### Who do I talk to? ###

* Samuel Fullman <samuel f at compass point media dot com>
* See you on the internet..