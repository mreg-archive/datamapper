* Add support for SplTypes to PDO\Expression

* Add hooks from mapper to model:
  if (is_callable($model, 'preSaveHook')) ...
    * preExtractHook
    * postExtractHook
    * preLoadHook
    * postLoadHook

* Refactor query building from `PDO\Table` to a builder object. In table use
  something simliar to
        $query = $this->getQueryBuilder()
        ->select()
        ->setColumns('*')
        ->setWhere($exprSet)
        ->getQuery();

* Add support for inter-mapper-relations. Create a Relation class for
  managing search conditions when working with the related mapper.
  Support for one-to-one and one-to-many using `hasOne` and `hasMany`

        // Load an one-to-many relation
        $mapper->hasMany('Address', $relationObj, $addressMapper);
        // Get all addresses
        $iterator = $mapper->getAddressIterator($model);
        // Insert or update an address
        $mapper->saveAddress($model, $address);
        // Remove relation to address, but do note delete address from db
        $memberMapper->disownAddress($member, $address);
        // Remove relation and remove address from db
        $memberMapper->purgeAddress($member, $address);

        // Same as above, but now getAddress returns a model, not an iterator
        $mapper->hasOne('Address', $relationObj, $addressMapper);
        $address = $mapper->getAddress($model);
