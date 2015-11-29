Read and convert data from dmoz.org
Uses the structure.u8.rdf and content.u8.rdf files from <http://rdf.dmoz.org>
as input, and writes them into a MySQL and as Turtle TTL files


=== dmoz2mysql ===

PHP commandline scripts to convert dmoz RDF to import into MySQL database

The dmoz RDF files are based on the outdated RDF/XML format, and very
hard to parse, and on top of that also non-standard. This makes
any standard triple store import tool choke.

Downloaded from <http://sourceforge.net/projects/dmoz2mysql/> as ZIP
Version 3.0
Author: Amir Salihefendic <mailto:amix@amix.dk>
Copyright: JFL Webcom <http://www.webcom.dk>
See README-dmoz2mysql.html for more info.

Edit config.php with your database info etc.
Then call on commandline: php start_script.php
It's a PHP console script, not a PHP web page application.


=== mysql2ttl ===

Python scripts by Ben Bucksch to read from MySQL and export to a Turtle ttl triple file

Turtle <http://www.w3.org/TR/turtle/> ttl files are the standard format for
large RDF / triple / LOD data dumps and can be easily imported into most
triple store databases.

Edit config.py with your database info.
Then call on commandline: python start.py

Future:
- I wish that the dmoz.org project would just offer ttl files as download
  instead of the malformed RDF/XML files. This would remove the need for
  mysql2ttl.
- A smaller improvement would be to modify class_parse.php here to
  directly write out TTL instead of executing SQL INSERT queries.
  This shouldnt be hard. But I don't want to touch PHP :).
- Until then, I hope this converter might be useful for somebody.


=== File sizes ===

rdf.dmoz.org download files:
*  85M  structure.rdf.u8.gz
* 247M  content.rdf.u8.gz
* 331M  total

extracted:
* 886M  structure.rdf.u8
* 1.7G  content.rdf.u8
* 2.5G  total

converter output:
*  22M  categories.ttl.gz
*  26M  category-hierarchy.ttl.gz
*  36M  links.ttl.gz
* 207M  link-titles.ttl.gz
* 290M  total

extracted:
* 175M  categories.ttl
* 592M  category-hierarchy.ttl
* 246M  links.ttl
* 919M  link-titles.ttl
* 1.9G  total
