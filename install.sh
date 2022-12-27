#!/bin/bash 

echo "Enter module namespace(Mycompany\\\\Core)"
read moduleNameSpace

echo "Enter module name(mycompany.core)"
read moduleName

moduleNameSpace2=${moduleNameSpace/\\/\\\\\\\\}
moduleNameSpace3=${moduleNameSpace/\\/_}
moduleNameSpace=${moduleNameSpace/\\/\\\\}

find ./mycompany.empty_module/ -name "*.php"|while read fname; do
  sed -i -e "s/MycompanyEmptyModule/$moduleNameSpace3/g;" -e "s/Mycompany[\\]\{2\}EmptyModule/$moduleNameSpace2/g;" -e "s/Mycompany[\\]EmptyModule/$moduleNameSpace/g;" -e "s/mycompany.empty_module/$moduleName/g" $fname
done

mv mycompany.empty_module $moduleName
