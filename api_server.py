from flask import Flask, request, jsonify
from flask_cors import CORS
app = Flask(__name__)
CORS(app)

# Example data for demonstration
example_faqs = [
    {"id": 1, "question": "What is a diesel-electric submarine?", "answer": "A submarine powered by diesel engines and electric batteries.", "category_name": "General"},
    {"id": 2, "question": "How deep can a WWII sub go?", "answer": "Typically around 300 feet.", "category_name": "General"},
]

@app.route('/api/corrected-faqs')
def corrected_faqs():
    action = request.args.get('action')
    if action == 'categories':
        return jsonify([{"id": 1, "name": "General", "description": "General submarine questions"}])
    elif action == 'faqs':
        return jsonify(example_faqs)
    elif action == 'search':
        q = request.args.get('q', '').lower()
        results = [faq for faq in example_faqs if q in faq['question'].lower() or q in faq['answer'].lower()]
        return jsonify(results)
    else:
        return jsonify([])

if __name__ == '__main__':
            app.run(port=5001)
