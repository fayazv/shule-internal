use shuledirect;
-- make sure foreign keys will be enforced
SET foreign_key_checks = 1;

-- add uniqueness constraint here for notes (parent_notes_id,content) since that cannot be done from Mysql Workbench
-- UNIQUE constraint has a byte-limit so use the first 500 characters from content
ALTER TABLE notes ADD CONSTRAINT unique_notes_content_per_parent UNIQUE (parent_notes_id,content(500));

-- prepare note_types
INSERT INTO note_types(name,depth) VALUES ('Project',0 ) ; 
INSERT INTO note_types(name,depth) VALUES ('Form',1 ) ; 
INSERT INTO note_types(name,depth) VALUES ('Subject',2 ) ; 
INSERT INTO note_types(name,depth) VALUES ('Topic',3 ) ; 
INSERT INTO note_types(name,depth) VALUES ('Subtopic',4 ) ; 
INSERT INTO note_types(name,depth) VALUES ('Concept',5 ) ; 
INSERT INTO note_types(name,depth) VALUES ('Paragraph',6 ) ; 

-- prepare media_types
INSERT INTO media_types(type,is_printable) VALUES ("Image",true);
INSERT INTO media_types(type,is_printable) VALUES ("YouTube",false);

-- prepare languages
INSERT INTO languages(language) VALUES ('English');

-- prepare notes
INSERT INTO notes(content,position,note_type_id,parent_notes_id,language_id) VALUES ("ShuleDirect",0,1,NULL,1);
INSERT INTO notes(content,position,note_type_id,parent_notes_id,language_id) VALUES ("Form 1",0,2,(select id from notes project where project.content = 'ShuleDirect' and project.parent_notes_id is null),1);
INSERT INTO notes(content,position,note_type_id,parent_notes_id,language_id) VALUES ("Form 2",1,2,(select id from notes project where project.content = 'ShuleDirect' and project.parent_notes_id is null),1);
INSERT INTO notes(content,position,note_type_id,parent_notes_id,language_id) VALUES ("Form 3",2,2,(select id from notes project where project.content = 'ShuleDirect' and project.parent_notes_id is null),1);
INSERT INTO notes(content,position,note_type_id,parent_notes_id,language_id) VALUES ("Form 4",3,2,(select id from notes project where project.content = 'ShuleDirect' and project.parent_notes_id is null),1);
