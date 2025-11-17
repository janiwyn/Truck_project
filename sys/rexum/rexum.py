import sys
import phpGen;
import func;

def generate_class(class_name, path):

    init =  "if (!AppData::__table_exists($this->TableName)):\n"
    init += '   \t\t\t$query = "CREATE TABLE `$this->TableName` (";\n'
    init += '   \t\t\t$query .= "`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,";\n'
    init += '   \t\t\t#$query .= "`field` VARCHAR(255) NOT NULL DEFAULT \'N/A\',";\n'
    init += '   \t\t\t$query .= "`created_at` timestamp NOT NULL DEFAULT current_timestamp(),";\n'
    init += '   \t\t\t$query .= "`updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()";\n'
    init += '   \t\t\t$query .= ") ENGINE=InnoDB";\n'
    init += '   \t\t\tAppData::__execute($query);\n'
    init += '\t\tendif;\n'

    std_comment = "Standard data format returned by this class"
    std ="$data = (object) $data;\n"
    std +="\t\treturn([\n"
    std +='\t\t\t"id"=>$data->id,\n'
    std +='\t\t\t"created_at"=>$data->created_at,\n'
    std +='\t\t\t"updated_at"=>$data->updated_at\n'
    std +="\t\t]);"
    
    folders = path.split("/")
    namespace="model"
    folder_path="../../model"
    for folder in folders:
        if folder!="model":
            namespace+=f"\{folder}"
            folder_path+=f"/{folder}"

    
    class_generator = phpGen.PHPClassGenerator(class_name, namespace)
    class_generator.add_property("private", "TableName='tbl_table_name'")
    class_generator.add_method("public", "__construct", "", "parent::__construct();")
    class_generator.add_method("private", "__std_data_format", "$data", std, std_comment)
    class_generator.add_method("private", "__singleton", "", "//Initialize all singleton objects here;")
    class_generator.add_method("private", "__init", "", init)
    php_class = class_generator.generate_php_class()

    # Save the generated PHP class to a file
    file_name = folder_path + "/" + class_name + ".php"
    with open(file_name, "w") as file:
        file.write(php_class)

    print("Class generated successfully!")


def generate_route(namespace, method_name):

    #extract class name from namespace
    path_arr = namespace.split("/")
    if len(path_arr)>0:
        class_name = path_arr[len(path_arr)-1]

        #Remove file extention if specified
        file_p = class_name.split(".")
        class_name = file_p[0]
        class_generator = phpGen.PHPClassGenerator(class_name, namespace)
        class_generator.create_api_route(method_name)

        print("*******END******")
    else:
        print("Invalid class path specified")

   

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python rexum.py <action> <properties>\n")
        print("Properties include:-\n")
        print("1. class: creates a new class. You must set <class *name> and <folder *path> within the model folder\n")
        print("2. route: creates a new route. You must set <Class *namespace: using '/'> <Method>\n")


    else:
        action = sys.argv[1]
        property = sys.argv[2]

        if action == "class":
            if len(sys.argv) != 4:
                print(f"Usage: python rexum.py class <class name> <folder path>")
            else:
               class_name = sys.argv[2]
               folder_path = sys.argv[3]
               print(f"Generating class: {class_name} at path {folder_path} *******")
               func.initialize_directory_path(f"../../model/{folder_path}")
               generate_class(class_name, folder_path)




        elif action == "route":
            print("Generating route *******")
            if len(sys.argv) != 4:
                print(f"Usage: python rexum.py route <Class Namespace> <Method>")
            else:
               namespace = sys.argv[2]
               method_name = sys.argv[3]
               print(f"Initializing route from: {namespace} for {method_name} *******")
               generate_route(namespace, method_name)

        else:
            print("Action not recorgnized *******")