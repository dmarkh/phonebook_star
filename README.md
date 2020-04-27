
# NEP/NP Collaboration PhoneBook

## Intro
PhoneBook: Essential tool to keep track of Collaboration's records
Reason for development: no opensource or commercial off-the-shelf products available on the market.. yet every HEP/NP collaboration needs one.

## Features beyond Excel spreadsheet:

 * Lists of Institutions, members – each entity has a list of dynamic fields attached
 * Version-controlled: changes of all entity fields are recorded and timestamped, new fields added on a fly using UI
 * AuthorList Generators: APS, IOP, INSPIRE formats
 * Statistics: graphs for all fields, worldmap of all participating institution locations

## Implementation:

 * Entities: Institution, Member, Field, FieldGroup
 * MySQL database backend (EAV model, schema-free) which has detailed historical information on every member of STAR collaboration. New fields could be configured by admin on a fly without any service downtime or schema updates
 * Web-based UI: admin UI + user UI, rich set of features
 * Provides RESTful API for integration with other experiment tools like ShiftSignup, ShiftLog

## License

STAR PhoneBook is covered under the terms of [MIT License](https://en.wikipedia.org/wiki/MIT_License)
