import func;
import os;
import re

class PHPClassGenerator:
    def __init__(self, class_name, namespace):
        self.class_name = class_name
        self.properties = []
        self.methods = []
        self.namespace = namespace
        self.method_name = ""
        self.route_name = ""


    def add_property(self, visibility, name):
        property_declaration = f"{visibility} ${name};"
        self.properties.append(property_declaration)

    def add_method(self, visibility, name, parameters, body, comment=''):
        if len(comment)>0:
            comment = f"/**\n\t* {comment}\n\t*/\n\t"
        method_declaration = f"{comment}{visibility} function {name}({parameters}) {{\n\t\t{body}\n\t}}"
        self.methods.append(method_declaration)

    def generate_php_class(self):
        php_class = "<?php\n"
        php_class += f"namespace {self.namespace};\n"
        php_class += "use model\App;\n"
        php_class += "use sys\store\AppData;\n\n"
        php_class += f"class {self.class_name} extends App{{\n\n"

        for property_declaration in self.properties:
            php_class += f"\t{property_declaration}\n"

        php_class += "\n"

        for method_declaration in self.methods:
            php_class += f"\t{method_declaration}\n\n"

        php_class += "}"

        return php_class


    def generate_api_file(self, file_name):

        folders = self.namespace.split("/")
        namespace = "model"
        for folder in folders:
            if folder!="model":
                namespace+=f"\{folder}"

        #Generate required parameters
        parameter_arr = func.generate_api_required_params(self.method_name)
        required = parameter_arr['required'] if len(parameter_arr['required'])>2 else ''
        passed = parameter_arr['passed'] if required else ''
        
        action_name=f"accessing route: {self.route_name}"
        api_string = "<?php\n"
        api_string += f"use {namespace};\n"
        api_string += "require_api_headers();\n"
        api_string += '$data=json_decode(file_get_contents("php://input"));\n'
        api_string += f"require_api_data($data, [{required}]);\n"
        api_string += f"$NewRequest=new {self.class_name};\n"
        api_string += f"$NewRequest->__set_system_user('{action_name}');\n"
        api_string += f"$result=$NewRequest->{self.method_name.split('(')[0]}({passed});\n"
        api_string += "$info = format_api_return_data($result, $NewRequest);\n\n//make json\n"
        api_string += "print_r(json_encode($info));\n\n ?>"

        with open(file_name, "w") as file:
            file.write(api_string)
        print("*M1. API file created successfully!")

    def generate_route_content(self, route_name, file_location):

        route_string = f'\tcase "{route_name}":\n'
        route_string += f'\t\tinclude_once "{file_location}";\n'
        route_string += '\t\texit;\n'
        route_string += '\t\tbreak;\n\n'
        return route_string

    def generate_route_file(self, file_name):
        
        route_string = "<?php\n"
        route_string += "switch($request):\n"
        route_string += "endswitch;\n"
        route_string +="?>"

        with open(file_name, "w") as file:
            file.write(route_string)
        print("*M2. Route file created successfully!")
    

    def create_api_route(self, action):
        
        print(f"Generating path from namespace {self.namespace} {action}")
        folders = self.namespace.split("/")
        folder_path = "../../model"
        api_path = "../../api"
        r_folder = []
        for folder in folders:
            if folder!="model":
                folder_path+=f"/{folder}"
                api_path += f"/{folder.lower()}"
                r_folder.append(folder)

        file_name = folder_path + ".php"
        result = func.extract_method_declaration(file_name, action)
        if result:
            self.method_name = result
            print("Method found:", result)

             # generate route file name
            route_file = f"route_{r_folder[0].lower()}.php"
            route_path = "../../routes/" + route_file

            api_file_name = func.extract_api_file_name(result)
            api_file = f"{api_path}/{api_file_name}.php"

            # generate route name
            route_name = func.generate_route_name(api_file_name, r_folder)
            self.route_name = route_name
            if func.find_in_file(route_path, route_name):
                    print("WARNING! Route name already exists\n\n")
                    return None


           
            func.print_colored(f"Route Name: {route_name}\n", 1)
            func.initialize_directory_path(api_path)


            self.generate_api_file(api_file)
            print(f"Generated API file: {api_file[6:]}\n")

           
            if os.path.isfile(route_path):
                print(f"Route file exists...using {route_path[6:]}\n")
                # Open the file and search for the route name
                # if route exists exit
               
                # create route
                func.add_to_switch_statement(route_path, self.generate_route_content(route_name, api_file[6:]))
               
            else:
                print("File does not exist....creating route file\n")
                self.generate_route_file(route_path)
                func.add_to_switch_statement(route_path, self.generate_route_content(route_name, api_file[6:]))
                print(f"Generated ROUTE file: {route_path[6:]}\n")
                func.add_to_main_router(route_path[6:])


        else:
            print("Method not found.")