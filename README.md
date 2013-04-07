About SourceForge
-----------------

SourceForge is a flexible code generator. It can generate basic code
from the model description. Use different schemes to get code as you wish.

Example
-------

```
model User scheme yii_model, yii_view_twitter_bs, yii_controller, mysql:
	required attr first_name char(100);
	attr last_name char(100);
	required attr email email;
	required attr role enum("Admin", "Editor", "Subscriber") = "Subscriber";
	attr active bool;
	collection attr posts Post;

model Post scheme yii_model, yii_view_twitter_bs, yii_controller, mysql:
	required attr title char(100);
	required attr `text` text;
	required attr status enum("Draft", "Trash", "Published") = "Draft";
	attr user User;
```

