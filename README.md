Read and convert data from dmoz.org
Uses the structure.u8.rdf and content.u8.rdf files from <http://rdf.dmoz.org>
as input, and writes them into a MySQL

= dmoz2mysql =

PHP Scripts to convert dmoz RDF to import into MySQL database

The dmoz RDF files are based on the outdated RDF/XML format, and very
hard to parse, and on top of that also non-standard. This makes
any standard triple store import tool choke.

Downloaded from <http://sourceforge.net/projects/dmoz2mysql/> as ZIP
Version 3.0
Author: Amir Salihefendic <mailto:amix@amix.dk>
Copyright: JFL Webcom <http://www.webcom.dk>
See README-dmoz2mysql.html for more info.

Edit config.php with your database info etc.
