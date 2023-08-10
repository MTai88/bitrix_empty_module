#!/bin/bash 

echo "Enter module namespace(Mycompany\\\\Core)"
read moduleNameSpace

echo "Enter module name(mycompany.core)"
read moduleName

moduleNameSpace2=${moduleNameSpace/\\/\\\\\\\\}
moduleNameSpace3=${moduleNameSpace/\\/_}
moduleNameSpace=${moduleNameSpace/\\/\\\\}

find ./mycompany.emptymodule/ -name "*.php"|while read fname; do
  sed -i -e "s/Mycompany_EmptyModule/$moduleNameSpace3/g;" -e "s/Mycompany[\\]\{2\}EmptyModule/$moduleNameSpace2/g;" -e "s/Mycompany[\\]EmptyModule/$moduleNameSpace/g;" -e "s/mycompany.emptymodule/$moduleName/g" $fname
done

mv mycompany.emptymodule $moduleName
