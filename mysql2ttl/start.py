#!/usr/bin/python
# Data export choices:
# * Use name path as URI (not catID)
# * do not output other languages (altlang)

from config import *
import mysql.connector
from urlparse import urlparse
import urllib
import gzip
import re



normalizeCatName1Regexp = re.compile("\/[A-Z0-9]$") # Just /A
normalizeCatName2Regexp = re.compile("\/[A-Z0-9]\/") # Just /A/
removeTagsRegexp = re.compile("&lt;.*?&gt;")

# Some categories have too many entries and thus
# use alphabetical subcategories.
# We will merge them.
def convertCatName(text):
  return normalizeCatName1Regexp.sub("",
      normalizeCatName2Regexp.sub("/", urllib.quote(text.encode('utf8'))))

def isAlphabethical(text):
  # match() returns None although subn() returns 1. No idea why.
  return normalizeCatName1Regexp.subn("", text)[1] > 0 or normalizeCatName2Regexp.subn("", text)[1] > 0

# Filters escaped HTML from the description
def convertDescription(text):
  return removeTagsRegexp.sub("", escape(text)
      .replace("&lt;p&gt;", "")) # <p> is very common, and very usless

def escape(text):
  return text.replace('"', "'").replace("\\", "")



# Main

#test
#print convertCatName("Starting/B/Title") # should give "Starting/Title"
#print normalizeCatName1Regexp.sub("", "Starting/B")
#print isAlphabethical("Starting/B") # test, should give "Starting/Title"
#print normalizeCatName1Regexp.subn("", "Starting/B")[1]

PREAMBLE = """
@prefix dmoz: <http://dmoz.org/rdf/> .
@prefix dmozcat: <http://dmoz.org/rdf/cat/> .
@prefix dc: <http://purl.org/dc/elements/1.0/> .

@prefix skos: <http://www.w3.org/2004/02/skos/core#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
dmoz:Topic owl:equivalentClass skos:Concept .
dmoz:narrow owl:equivalentProperty skos:narrower .

"""


db = mysql.connector.connect(**dbConfig)
query = db.cursor()

catFile = gzip.open("categories.ttl.gz", "wt")
catFile.write(PREAMBLE)

print "Converting categories"
query.execute("SELECT name, catid, title, description FROM structure")
for (name, catid, title, description) in query:
  if (catid == 1):
    name = "Root"
    title = "Root"
  if (isAlphabethical(name)): # Avoid to output /B categories at all
    continue
  catFile.write((u'dmozcat:%s\n  dmoz:catid %d ;\n  dc:Subject "%s" ;\n  dc:Description "%s" .\n'
      % (convertCatName(name), catid, escape(title).replace("_", " "), convertDescription(description))).encode("utf8"))

print "Converting category hierarchy"
query.execute("SELECT name, type, resource FROM datatypes LEFT JOIN structure USING (catid)")
for (name, type, resource) in query:
  if (name == ""):
    name = "Root"
  catFile.write((u'dmozcat:%s dmoz:%s dmozcat:%s .\n'
      % (convertCatName(name), type, convertCatName(resource))).encode("utf8"))

catFile.close()
linksFile = gzip.open("links.ttl.gz", "wt")
linksFile.write(PREAMBLE)

print "Converting links"
query.execute("SELECT topic, type, resource FROM content_links")
lastTopic = "dummy"
# dummy rule, just to avoid a lonely dot at the start
linksFile.write(u'dmoz:link owl:equivalentProperty <http://dbpedia.org/ontology/wikiPageExternalLink> ')
for (topic, type, resource) in query:
  # triples (longer syntax, simpler code):
  # linksFile.write((u'dmozcat:%s dmoz:%s <%s> . \n'
  #     % (convertCatName(topic), type, resource)).encode("utf8"))
  if topic == lastTopic:
    linksFile.write((u' ;\n').encode("utf8"))
  else:
    lastTopic = topic
    linksFile.write((u' .\ndmozcat:%s\n' % (convertCatName(topic))).encode("utf8"))
  linksFile.write((u' dmoz:%s <%s>' % (type, resource)).encode("utf8"))
linksFile.write(u' .\n\n')

print "Converting link titles"
query.execute("SELECT externalpage, title, description FROM content_description")
for (externalpage, title, description) in query:
  hostname = urlparse(externalpage).hostname.replace("www.", "")
  linksFile.write((u'<%s>\n  dmoz:domain "%s";\n  dc:Title "%s";\n  dc:Description "%s" .\n'
      % (externalpage, hostname, escape(title), escape(description))).encode("utf8"))

linksFile.close()
query.close()
db.close()
