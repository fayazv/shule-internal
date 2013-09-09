-- prepare note_types
INSERT INTO note_types(name,depth) VALUES ('Project',0 ) ; 
INSERT INTO note_types(name,depth) VALUES ('Form',1 ) ; 
INSERT INTO note_types(name,depth) VALUES ('Subject',2 ) ; 
INSERT INTO note_types(name,depth) VALUES ('Topic',3 ) ; 
INSERT INTO note_types(name,depth) VALUES ('Subtopic',4 ) ; 
INSERT INTO note_types(name,depth) VALUES ('Concept',5 ) ; 
INSERT INTO note_types(name,depth) VALUES ('Paragraph',6 ) ; 

-- prepare languages
INSERT INTO languages(language) VALUES ('English');

-- prepare notes
INSERT INTO notes(content,position,note_type_id,parent_notes_id,language_id) VALUES ("ShuleDirect",0,1,NULL,1);
