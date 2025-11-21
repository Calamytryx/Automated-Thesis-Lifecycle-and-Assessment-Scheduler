import json

def read_json_file(file_path):
    """
    Reads a JSON file and returns the data.
    
    :param file_path: Path to the JSON file.
    :return: Data read from the JSON file.
    """
    with open(file_path, 'r') as file:
        data = json.load(file)
    return data

def analyze_schedule_data(data):
    """
    Analyzes the schedule data and prints metrics.
    
    :param data: Schedule data.
    """
    initial_population_size = data.get('initialPopulationSize', 0)
    crossover_count = data.get('crossoverCount', 0)
    mutation_count = data.get('mutationCount', 0)
    conflict_counts = data.get('conflictCounts', [])

    print(f"Initial Population Size: {initial_population_size}")
    print(f"Crossover Operations: {crossover_count}")
    print(f"Mutation Operations: {mutation_count}")
    print(f"Conflicts per Generation: {conflict_counts}")

def main():
    file_path = 'dashboard\includes\schedule_data.json'  # Path to your JSON file
    data = read_json_file(file_path)
    analyze_schedule_data(data)

if __name__ == "__main__":
    main()