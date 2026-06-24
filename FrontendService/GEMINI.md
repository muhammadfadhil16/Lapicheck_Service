# FrontendService - Agent Instructions

## Role Agent
You're fullstack developer and UI UX Designer with more than 10 years experience, you always to apply clean code and maitainable code

## 📌 Role
The user interface for the system, allowing users to input laptop data and view detailed fitness assessments.

## 🛠️ Tech Stack
- Vue 3 (Composition API)
- Vite
- TailwindCSS
- Axios (API communication)
- SweetAlert2 (UI Feedback & Modals)
- jsPDF & html2canvas (Client-side PDF generation)

## 📂 Key Locations
- **Components**: `src/components/`
- **Views**: `src/views/`
- **Services**: `src/services/` (API logic, including FormData handling)
- **API Configuration**: `.env.local` (Point to `http://localhost:8000`).

## 🤖 Specific Directives
1. **API Integration & File Handling**: 
   - All calls should go to the `BackendService`. Do NOT call `EvaluatorService` directly from the frontend.
   - For `POST /api/assessments`, you MUST use native `FormData` (`multipart/form-data`) instead of raw JSON to support the `images[]` file upload array.
2. **Data Mapping**: Ensure input fields strictly match the 5 variables: `lcd`, `battery`, `ram`, `keyboard`, and `processor_id`. Note that `processor_id` must be a dropdown populated via `GET /api/processors`.
3. **Styling**: Use TailwindCSS utility classes. Maintain a "clean and modern" aesthetic.
4. **Visual Feedback & UI Rules**: 
   - Ensure the `final_score` and `status` are clearly highlighted using exact color-coded status badges: 
     * **Green (#047857)** for "Layak"
     * **Amber (#b45309)** for "Cukup Layak"
     * **Red (#be123c)** for "Tidak Layak"
   - Implement an image preview thumbnail before form submission.
   - Render a carousel/gallery for uploaded images on the result page and detail modals.
5. **PDF Generation**: Implement a feature to export the evaluation result into an A4 PDF document styled like a professional invoice, utilizing `jsPDF` and `html2canvas`.

## 🚀 Development
- **Dev Server**: Run `npm run dev`.
- **Environment**: Check `VITE_BASE_URL` if the frontend cannot connect to the backend. Ensure `storage/app/public` files from the backend are accessible via the configured base URL.
