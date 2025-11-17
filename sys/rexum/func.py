import re
import os;
def generate_api_required_params(method_name):
    param_pattern = re.compile(r'\((.*?)\)')
    matches = re.findall(param_pattern, method_name)
    parameter_arr = {
        'required':"", 
        'passed':""
        }
    required = []
    passed = []
    passer =""
    if matches:
        parameters = matches[0].split(',')
        parameters = [param.strip() for param in parameters]
        for param in parameters:
            #Remove $
            param = param[1:]
            required.append(f'"{param}"')
            passed.append(f"clean($data->{param})")
            passer =  ",\n\t\t\t\t\t\t\t\t\t".join(passed)
            passer = f"\n\t\t\t\t\t\t\t\t\t{passer}\n"
        parameter_arr["passed"]=passer
        parameter_arr["required"]= ", ".join(required)

    return parameter_arr


def extract_method_declaration(file_path, method_name):
     if not os.path.isfile(file_path):
        print(f"Can't location class file {file_path} not found...exiting\n")
        return None
    
     with open(file_path, 'r') as file:
        content = file.readlines()
        keyword = f"function {method_name}"
        # Find the line number with "endswitch"
        match = None
        for i, line in enumerate(content):
            if keyword in line:
                match = i
                extraction = line.strip()[:-1]
                extraction = extraction.split('_', 1)
                method_name = "_" + extraction[1]
                signature = method_name if len(extraction) > 1 else ''
                print(f"Method signature: {signature}\n")
                return signature
    
        return match

def get_method_name(file_path, class_name, method_name):
    if not os.path.isfile(file_path):
        print(f"{file_path} not found...exiting\n")
        return None
    with open(file_path, 'r') as file:
        content = file.read()
        # Regular expression pattern to match the method declaration
        # pattern = r"public\s+function\s" + method_name + r"\s*\("
        pattern = r"function\s" + method_name + r"\s*\((.*?)\)\s*\{"

        # Regular expression pattern to match the class declaration
        class_pattern = r"class\s" + class_name + r"\s+extends\s+(\w+)\s*\{"

        # Find the class declaration position
        class_match = re.search(class_pattern, content)
        if class_match:
            print("Class found!\n")
            class_start = class_match.start()
            class_end = class_match.end()

            # Find the method declaration position within the class
            method_match = re.search(pattern, content)
            if method_match:
                # method_start = method_match.start()
                # method_end = method_match.end()

                # Extract the method name
                # method = content[class_start + method_start:class_start + method_end]
                method_line = method_match.group(0).split(" ")


                return method_line[1]

        else:
            print("Class not found")
    return None

def extract_api_file_name(method_name):
    #Eliminate the parameters using the first bracket
    method_name = method_name.split("(")[0]

    #remove the first two underscores
    file_name = method_name[2:].lower()

    return file_name

def generate_route_name(file_name, folder_array):
    
    if len(folder_array)==0:
        return file_name
    route_f = []
    for folder in folder_array:
        folder_name = folder.lower()
        route_f.append(folder_name)

    # Append file_name
    route_f.append(file_name)
    delimeter = "/"
    route_name = delimeter.join(route_f)

    return route_name

def add_to_switch_statement(file_name, case_content):
    # Read the PHP file
    with open(file_name, 'r') as file:
        content = file.readlines()

    # Find the line number with "endswitch"
    end_switch_line = None
    for i, line in enumerate(content):
        if 'endswitch' in line:
            end_switch_line = i
            break

    # Insert content just above the "endswitch" line
    if end_switch_line is not None:
        new_content = content[:end_switch_line]
        new_content.append(case_content)
        new_content.extend(content[end_switch_line:])
        content = new_content

    # Write the modified content back to the PHP file
    with open(file_name, 'w') as file:
        file.writelines(content)

def find_in_file(file_name, keyword):
    # Read the PHP file
    if not os.path.isfile(file_name):
        print(f"Can't search file {file_name} not found...exiting\n")
        return None
    with open(file_name, 'r') as file:
        content = file.readlines()

    # Find the line number with "endswitch"
    match = None
    for i, line in enumerate(content):
        if keyword in line:
            match = i
            return match
    
    return match


def initialize_directory_path(file_path):
    try:
        if not os.path.exists(file_path):
            os.makedirs(file_path)
            print("Folder created successfully.")
        else:
            print("Folder already exists.")
    except Exception as e:
            print("An error occurred:", str(e))



def add_to_main_router(route_path):
    # Read the PHP file
    file_name ="../../App.php"
    with open(file_name, 'r') as file:
        content = file.readlines()

    # Find the line number with "endswitch"
    end_switch_line = None
    for i, line in enumerate(content):
        if 'api/404.php' in line:
            end_switch_line = i
            break

    # Insert content just above the "api/404.php" line
    route_content=f'\tinclude_once "{route_path}";\n'
    if end_switch_line is not None:
        new_content = content[:end_switch_line]
        new_content.append(route_content)
        new_content.extend(content[end_switch_line:])
        content = new_content

    # Write the modified content back to the PHP file
    with open(file_name, 'w') as file:
        file.writelines(content)

    print("Modified App entry point\n")

def print_colored(text, color_number):
    # ANSI escape sequence for color formatting
    COLOR_RED = '\033[91m'
    COLOR_GREEN = '\033[92m'
    COLOR_YELLOW = '\033[93m'
    COLOR_BLUE = '\033[94m'
    COLOR_RESET = '\033[0m'
    colors = [
        '\033[91m',
        '\033[92m',
        '\033[93m',
        '\033[94m',
        '\033[0m'
    ]

    print(colors[color_number] + text + COLOR_RESET)
   

