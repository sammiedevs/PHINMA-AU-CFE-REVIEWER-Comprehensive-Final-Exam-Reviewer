import openai
import sys
import json

# Set your OpenAI API key
openai.api_key = "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"  # Replace with your actual OpenAI API key

def generate_questions(content):
    """
    Generates multiple-choice questions based on the given content using OpenAI's GPT model.
    """
    try:
        # Define the prompt for GPT
        prompt = f"""
        Generate 5 multiple-choice questions based on the following content. Each question should have 4 options and a correct answer.

        Content: {content}

        Format the output as a JSON array of objects, where each object has the following structure:
        {{
            "question": "The generated question",
            "options": ["Option 1", "Option 2", "Option 3", "Option 4"],
            "answer": "Correct Option"
        }}
        """

        # Call the OpenAI API
        response = openai.ChatCompletion.create(
            model="gpt-3.5-turbo",  # Use GPT-3.5 or GPT-4
            messages=[
                {"role": "system", "content": "You are a helpful assistant that generates multiple-choice questions."},
                {"role": "user", "content": prompt}
            ],
            max_tokens=500,  # Adjust based on the length of the content
            temperature=0.7  # Controls creativity (0 = deterministic, 1 = creative)
        )

        # Extract the generated questions
        generated_text = response['choices'][0]['message']['content']
        questions = json.loads(generated_text)  # Parse the JSON string into a Python list
        return questions

    except Exception as e:
        # Handle errors
        print(f"Error generating questions: {e}", file=sys.stderr)
        return []

# Main execution
if __name__ == "__main__":
    # Read content from command-line arguments
    if len(sys.argv) < 2:
        print("Usage: python generate_exam.py <content>", file=sys.stderr)
        sys.exit(1)

    content = sys.argv[1]  # Get content from the command line
    questions = generate_questions(content)

    # Print the generated questions as JSON
    print(json.dumps(questions, indent=4))