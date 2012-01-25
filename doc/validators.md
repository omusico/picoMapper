Validators
==========


Customize validators error messages
-----------------------------------

Customize validators messages can be useful for localization. 
Inside your model, you need to fill the property `$validatorMessages`. 

Example:

    $this->validatorMessage = array(
        'required' => 'Valeur obligatoire',
        'unique' => 'Ce champ doit être unique'
    );

Use custom validators
---------------------

1. Create a directory named `validators` inside your projects
2. Create a new file, example: `phone.php`
3. Define a new class, example: `PhoneValidator`
4. Your class must be inside the namespace `\picoMapper\Validators`
5. Your validator must follow the interface `ValidatorInterface`

