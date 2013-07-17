<?php

/**
* NOTE: This code is purely meant for testing purposes and was put together in a 
* hackish manner. Please do not use this as production code.
* NOTE: subject id is a parameter that's passed in due to its uniqueness in naming the files. 
* In our API we won;t need this parameter
*/


class Notes
{
   public function save($subject_id, $augmented_notes)
   {
      //save the serialized array version into a file
      $success = file_put_contents(DATA_PATH."/{$subject_id}.txt", serialize($augmented_notes));
      
      //if saving was not successful, throw an exception
      if( $success === false ) 
      {
         throw new Exception('Failed to save notes item');
      }
   }

   public function set_augmented_notes($subject_id, $augmented_notes)
   {
      $subject_notes_array = json_decode($augmented_notes, true);
      for ($subject_notes_array as $topic) 
      {
         if (!array_key_exists('id', $topic)
         {
            $topic["id"] = time();
         } 

         if (array_key_exists('children', $topic)) 
         {
            for ($subject_notes_array['children'] as $sub_topic)
            {
               if (!array_key_exists('id', $sub_topic)
               {
                  $sub_topic["id"] = time();
               } 

               if (array_key_exists('children', $sub_topic))
               {
                  for ($sub_topic['children'] as $concept)
                  {
                     if (!array_key_exists('id', $concept)
                     {
                        $concept["id"] = time();
                     } 

                     if (array_key_exists('children', $concept))
                     {
                        for ($concept['children'] as $paragraph)
                        {
                           if (!array_key_exists('id', $paragraph)
                           {
                              $paragraph["id"] = time();
                           }
                        }
                     }
                  }
               }   
            }
         }
      }
      $new_augmented_notes = json_encode($subject_notes_array);
      save($subject_id, $new_augmented_notes);
      return $new_augmented_notes;
   }
   
   public function getAugmentedNotes($subject_id)
   {
      if( file_exists(DATA_PATH."/{$subject_id}.txt") === false ) {
         throw new Exception('Subject ID is invalid');
      }
      
      $augmented_notes_serialized = file_get_contents(DATA_PATH."/{$subject_id}.txt");
      $augmented_notes_json = unserialize($notes_item_serialized);
      
      
      return $augmented_notes_json;
   }


   public function editContent($subject_id, $item_id, $content)
   {
      $subject_notes_array = json_decode(getAugmentedNotes($subject_id), true);
      for ($subject_notes_array as $topic) 
      {
         if (array_key_exists('id', $topic)
         {
            if ($topic["id"] == $itemId)
            {
               $topic["content"] = $content;
            }
         } 

         if (array_key_exists('children', $topic)) 
         {
            for ($subject_notes_array['children'] as $sub_topic)
            {
               if (array_key_exists('id', $sub_topic)
               {
                  if ($sub_topic["id"] == $itemId)
                  {
                     $sub_topic["content"] = $content;
                  }
               } 

               if (array_key_exists('children', $sub_topic))
               {
                  for ($sub_topic['children'] as $concept)
                  {
                     if (array_key_exists('id', $concept)
                     {
                        if ($concept["id"] == $itemId)
                        {
                           $concept["content"] = $content;
                        }
                     } 

                     if (array_key_exists('children', $concept))
                     {
                        for ($concept['children'] as $paragraph)
                        {
                           if (array_key_exists('id', $paragraph)
                           {
                              if ($paragraph["id"] == $itemId)
                              {
                                 $paragraph["content"] = $content;
                              }
                           }
                        }
                     }
                  }
               }   
            }
         }
      }
      $new_augmented_notes = json_encode($subject_notes_array);
      save($subject_id, $new_augmented_notes);
      return $new_augmented_notes;
   }

    
   public function deleteContent($subject_id, $itemId)
   {
      $subject_notes_array = json_decode(getAugmentedNotes($subject_id), true);
      for ($subject_notes_array as $topic) 
      {
         if (array_key_exists('id', $topic)
         {
            if ($topic["id"] == $itemId)
            {
               unset($topic);
               break;
            }
         } 

         if (array_key_exists('children', $topic)) 
         {
            for ($subject_notes_array['children'] as $sub_topic)
            {
               if (array_key_exists('id', $sub_topic)
               {
                  if ($sub_topic["id"] == $itemId)
                  {
                     unset($sub_topic);
                     break;
                  }
               } 

               if (array_key_exists('children', $sub_topic))
               {
                  for ($sub_topic['children'] as $concept)
                  {
                     if (array_key_exists('id', $concept)
                     {
                        if ($concept["id"] == $itemId)
                        {
                           unset($concept);
                           break;
                        }
                     } 

                     if (array_key_exists('children', $concept))
                     {
                        for ($concept['children'] as $paragraph)
                        {
                           if (array_key_exists('id', $paragraph)
                           {
                              if ($paragraph["id"] == $itemId)
                              {
                                 unset($paragraph);
                                 break;
                              }
                           }
                        }
                     }
                  }
               }   
            }
         }
      }
      $new_augmented_notes = json_encode($subject_notes_array);
      save($subject_id, $new_augmented_notes);
      return $new_augmented_notes;
   }
   
   TODO: add Tags, Media, AddContent

}
