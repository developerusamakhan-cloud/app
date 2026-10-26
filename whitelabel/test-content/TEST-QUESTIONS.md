# Chatbot test with the Brightway Dental sample

Brightway Dental Clinic is a made-up business. Use it to check that the chatbot learns from an uploaded file.

## Steps

1. In WordPress open the chatbot menu, then **Extra knowledge**.
2. Under the file upload, choose `brightway-dental-sample.pdf` (or the `.docx`) and click **Upload**. Upload only one of the two, they have the same text.
3. Optional: add the custom answers below under **Extra knowledge > Custom answers**.
4. Open the website and ask the questions below in the chat.

## Questions the chatbot should answer

| Ask this | The answer should say |
|---|---|
| What are your opening hours on Saturday? | 10:00 AM to 2:00 PM |
| Are you open on Sunday? | No, closed on Sunday |
| How much is teeth whitening? | $350 |
| What does a root canal cost? | From $650 |
| Who does braces? | Dr Omar Reyes |
| Who is the lead dentist? | Dr Sara Malik |
| How do I book an appointment? | Phone (555) 014-2290, email hello@brightway-dental.example, or leave details in the chat |
| I have bad tooth pain at night, what do I do? | Emergency line (555) 014-2299, open 8:00 AM to 10:00 PM |
| What if I cancel late? | Cancel 24 hours before, otherwise a $30 charge |
| Which insurance do you take? | Delta Care, SmileFirst and HealthPlus |
| Do you have payment plans? | Yes, 6 or 12 months with no interest, for treatments over $1,000 |
| Is there parking? | Free parking behind the building |
| Can I bring my 2 year old? | Yes, children from age 2, and the first visit under age 3 is free |
| Do you speak Spanish? | Yes: English, Spanish, French and Polish |
| Does whitening hurt? | Most feel no pain; some feel mild sensitivity for a day or two |

## Test other languages

Ask the same questions in another language. The answer should come back in that language.

- Spanish: `¿Cuánto cuesta el blanqueamiento dental?`
- French: `Êtes-vous ouverts le samedi ?`
- German: `Gibt es Parkplätze?`

## Questions the chatbot should NOT know

The file does not cover these. The chatbot should say it does not know, or offer to take your details. It should not make up an answer.

- Do you do dental implants?
- What is Dr Malik's home address?
- Are you open on Christmas Eve?
- What is the Wi-Fi password?

## Test lead capture

Say: `I want to book a whitening appointment, please call me.`
The chat should offer the lead form. Fill it in, then check the chatbot menu > **Leads**.

## Custom answers to add (optional)

Add these under **Extra knowledge > Custom answers**. A custom answer wins over the file, so you can see the difference.

| Question | Answer |
|---|---|
| Do you offer dental implants? | Not yet. We plan to start offering implants next year. Leave your details and we will let you know. |
| Is the clinic open on Christmas Eve? | Yes, from 9:00 AM to 12:00 PM. |
| Do you have a discount for students? | Yes, students get 10% off check-ups with a valid student card. |

After adding them, ask the same questions again. The answers should now match the custom answers.

## Check the results in the admin

- **Conversations**: every chat you had.
- **Unanswered**: the questions the chatbot could not answer. Add an answer there and it is used from the next chat.
- **Analytics**: chat count and the most asked questions.
