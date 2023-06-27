find ./ -type f -exec sed -i -e 's|Zume_Simulator|Zume_Simulator|g' {} \;
find ./ -type f -exec sed -i -e 's|zume_simulator|zume_simulator|g' {} \;
find ./ -type f -exec sed -i -e 's|zume-simulator|zume-simulator|g' {} \;
find ./ -type f -exec sed -i -e 's|zume_simulator_post_type|zume_simulator_post_type|g' {} \;
find ./ -type f -exec sed -i -e 's|Zúme Critical Path|Zúme Critical Path|g' {} \;
mv zume-simulator.php zume-simulator.php
rm .rename.sh
